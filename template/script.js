let firstLoad = true;

$(document).ready(function () {
  $.ajax({
    method: "GET",
    url: "wp-json/quote-reader/v1/quotes/",
    data: {},
    cache: false,
    success: function (data) {
      localStorage.setItem('quotes', JSON.stringify(data));
      items = localStorage.getItem('quotes');
      init(items);
    }
  })

  $('#filterTopic').on('change', function () {
    $('#filterName').prop('selectedIndex', 0)
    var value = this.value;
    var filter = {
      'topic': value
    }
    updateFilter(filter);
    var myList = filterBy();
    var html = loopBoxes(myList);
    createMasonry(html);
  })

  $('#filterName').on('change', function () {
    $('#filterTopic').prop('selectedIndex', 0)
    var value = this.value,
      strArray = value.split(" "),
      lastNamePos = strArray.length - 1;
    /* If I have 2 names concat them */
    if (strArray.length > 2) {
      for (var i = 1; i < strArray.length - 1; i++) {
        strArray[0] = strArray[0].concat(" " + strArray[i]);
      }
    }

    var filter = {
      'name': strArray[0],
      'surname': strArray[lastNamePos]
    }
    updateFilter(filter);
    var myList = filterBy();
    var html = loopBoxes(myList);
    createMasonry(html);
  })

  $('#launch').on('click', function () {
    $('#filterName').prop('selectedIndex', 0)
    $('#filterTopic').prop('selectedIndex', 0)
    var value = $('#search').val();
    var filteredQuotes = JSON.parse(localStorage.getItem('quotes'));

    console.log("search for %s", value);

    var result = filteredQuotes.filter(function (single) {
      var strRegExPattern = '\\b' + value + '\\b';
      var pattern = new RegExp(strRegExPattern, "i")
      if (single.quote.match(pattern)) {
        return single;
      }
    })
    var html = loopBoxes(result);
    createMasonry(html);
  })
})

let init = function (mylist) {
  var htmlList = "",
    htmlNS = "",
    arrayNames = [],
    htmlT = "",
    arrayTopic = [];

  $.each(JSON.parse(mylist), function (id, i) {
    var ns = $.trim(i.name) + ' ' + $.trim(i.surname),
      topic = $.trim(i.topic)
    quote = $.trim(i.quote);;
    if (!arrayNames.includes(ns)) {
      arrayNames.push(ns);
      htmlNS += createOpt(ns);
    }
    if (!arrayTopic.includes(topic)) {
      arrayTopic.push(topic);
      htmlT += createOpt(topic);
    }
    htmlList += createBox(quote, ns, topic);
  });

  $("#filterTopic").append(htmlT);
  $("#filterName").append(htmlNS);
  createMasonry(htmlList);
}

let createBox = function (quote, author, topic) {
  var html = '<div class="card-box">' +
    '<div class="card card-with-border">' +
    '<div class="content">' +
    '<div class="qr-topic">' + topic + '</div>' +
    '<div class="qr-quote">' + quote + '</div>' +
    '<div class="qr-author">' + author + '</div>' +
    '</div>' +
    '</div>' +
    '</div>';
  return html;
}

let loopBoxes = function (list) {
  var html = '';
  $.each(list, function (pos, i) {
    var author = i.name + ' ' + i.surname;
    html += createBox(i.quote, author, i.topic);
  })
  return html;
}

let createOpt = function (key, val = key) {
  html = '<option value="' + key + '">' + val + '</option>';
  return html;
}

let updateFilter = function (data) {
  localStorage.setItem('filter', JSON.stringify(data));
}

function filterBy() {
  var filteredQuotes = JSON.parse(localStorage.getItem('quotes'));
  var filter = localStorage.getItem('filter');
  $.each(JSON.parse(filter), function (key, value) {
    filteredQuotes = filteredQuotes.filter(quotes => quotes[key] === value);
  });
  return filteredQuotes;
}

let createMasonry = function (html) {
  $(".qr-grid").html(html);
  if (firstLoad) {
    $('.qr-grid').masonry({
      columnWidth: '.card-box',
      itemSelector: '.card-box',
    });
    firstLoad = false;
  } else {
    $('.qr-grid').masonry('destroy');
    $('.qr-grid').masonry({
      columnWidth: '.card-box',
      itemSelector: '.card-box',
    })
  }
}