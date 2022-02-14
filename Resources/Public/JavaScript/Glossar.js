define(['jquery'], function ($) {

  var select = document.querySelector('#supportedLanguagePairs');

  if (select) {
    select.addEventListener('change', function (event) {
      var pair = event.target.value.split(',');
      document.querySelector('[name*=sourceLang]').value = pair[0];
      document.querySelector('[name*=targetLang]').value = pair[1];
    })
  }

  var addEntryButton = $('#addEntry');
  var entries = $('#entries');
  var lastId = 0;

  if(entries) {
    lastId = entries.children().length;
    entries.on('click', '.btn-danger', function() {
      $(this).closest('.row').remove();
    });
  }

  if (addEntryButton) {
    addEntryButton.click(addEntry)
  }

  function createRow(id) {
    var $row = $('<div class="row">\n' +
      '    <div class="col-xs-5">\n' +
      '        <div class="form-group ">\n' +
      '            <input type="text" placeholder="Source" class="form-control">\n' +
      '        </div>\n' +
      '    </div>\n' +
      '    <div class="col-xs-5">\n' +
      '        <div class="form-group">\n' +
      '            <input type="text" placeholder="Target" class="form-control">\n' +
      '        </div>\n' +
      '    </div>\n' +
      '    <div class="col-xs-2">\n' +
      '        <button type="button" class="btn btn-danger">-</button>\n' +
      '    </div>\n' +
      '</div>');

    $row.find('[placeholder="Source"]').attr('name', 'glossar[entries]['+ id +'][0]');
    $row.find('[placeholder="Target"]').attr('name', 'glossar[entries]['+ id +'][1]');

    return $row;
  }

  function addEntry(event) {
    event.preventDefault();
    var $row = createRow(lastId);
    lastId++;
    entries.append($row);
  }



});

