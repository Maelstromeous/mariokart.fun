var platform = $('#championship').attr('data-platform');

$('#in-progress').hover(function () {
  $(this).html('Finished?');
  $(this).removeClass('pulsate btn-warning');
  $(this).addClass('btn-success');
}, function () {

  $(this).html('In progress <i class="fa fa-spinner fa-spin"></i>');
  $(this).removeClass('btn-success');
  $(this).addClass('pulsate btn-warning');
});

$('#in-progress').click(function (event) {
  var data = {
    championship: $('#championship').attr('data-id'),
  };
  var button = $(this);

  $.ajax({
    url: baseUrl + currentPath + '/finalize',
    type: 'POST',
    dataType: 'JSON',
    data: JSON.stringify(data),
    timeout: 5000,
  })
  .done(function (returned) {
    console.log('done');
    console.log(returned);
    if (!returned.success || returned.success !== 'success') {
      alert('Unexpected response from server.... contact the developer!');
    }

    $(button).removeClass('pulsate btn-danger btn-warning').addClass('btn-success');
    $(button).find('i').removeClass('fa-refresh fa-spin').addClass('fa-check');
    $(button).html('Finished! <i class="fa fa-check"></i>');

    // TEMPORORY: Reload the page so the new stage can be applied
    // @todo: Make dynamic by updating points table, adding in a new stage etc
    window.location.reload();
  })
  .fail(function (xhr, textStatus, error) {
    var message = xhr.responseJSON;
    if (message && message.error) {
      alert(message.error);
    } else {
      alert("Unknown server error occured! :'(");
    }

    $(button).find('i').removeClass('fa-refresh fa-spin').addClass('fa-exclamation-triangle');
    $(button).removeClass('pulsate').addClass('btn-danger');
  });
});

$('#new-stage select[name="track"]').change(function (event) {
  var val = $(this).val();
  var parent = $(this).parents('#new-stage');

  // Update image
  var image = parent.find('img').first();
  if (val > 0) {
    image.attr('src', assetUrl + '/img/tracks/' + val + '.jpg' + version);
  } else {
    image.attr('src', assetUrl + '/img/tracks/new.jpg' + version);
  }

  // Update stage name
  if (val > 0) {
    parent.find('.stage-name').html(' - ' + $(this).find('option:selected').text());
  } else {
    parent.find('.stage-name').html('');
  }
});

$('#new-stage .save').click(function (event) {
  var data = {
    players: [],
  };
  var valid = true;
  var parent = $(this).parents('#new-stage');
  var button = $(this);

  // Reset any validation stuff
  parent.find('select').each(function (index, el) {
    $(el).css('background-color', '');
  });

  // Check that we have a track selected
  if (parent.find('select[name="track"]').val() == 0) {
    parent.find('select[name="track"]').css('background-color', '#ff8585');
    parent.find('#errors').html('Please select a track').slideDown();
    return false;
  }

  // Check for valid data and populate params if so
  var positions = parent.find('select[name^="player-"]');

  // First check that the number of positions equal the number of players
  var count = 0;
  parent.find('select[name^="player-"]').each(function (index, el) {
    if ($(this).val() != '0') {
      count++;
    }
  });

  var positions = $('#new-stage').find('.positions select');
  console.log(positions);
  if (count != positions.length) {
    $(positions).each(function (index, el) {
      if ($(el).val() == '0') {
        $(el).css('background-color', '#ff8585');
      }
    });

    parent.find('#errors').html('A player\'s position is missing. ' +
      'Please correct and try again.').slideDown();
    valid = false;
    return false;
  }

  // Check to make sure we're not assigning the same position to more than 1 player
  var assigned = [];
  var occurances = [];
  $(positions).each(function (index, el) {
    assigned.push($(this).val());
  });

  $(assigned).each(function (key, value) {
    var check = $.grep(assigned, function (elem) {
      return elem === value;
    });

    if (check.length > 1) {
      parent.find('#errors').html('Posistion #' + value + ' assigned more ' +
      'than once! Please correct and try again').slideDown();
      valid = false;
    }
  });

  console.log(valid);

  // If all valid, send the ajax
  if (valid === true) {
    parent.find('#errors').slideUp();
    $(this).addClass('pulsate');
    $(this).removeClass('btn-danger btn-success');
    $(this).find('i').removeClass('fa-exclamation-triangle fa-save').addClass('fa-refresh fa-spin');

    var data = {
      championship: $('#championship').attr('data-id'),
      track: parent.find('select[name="track"]').val(),
    };

    // Populate the player data
    data.players = [];

    $(positions).each(function (index, el) {
      data.players.push({
        id: $(el).parents('tr').first().attr('player-id'),
        pos: $(el).val(),
      });
    });

    $.ajax({
      url: baseUrl + currentPath + '/new-stage',
      type: 'POST',
      dataType: 'JSON',
      data: JSON.stringify(data),
      timeout: 5000,
    })
    .done(function (returned) {
      console.log('done');
      console.log(returned);
      if (!returned.success || returned.success !== 'success') {
        parent.find('#errors').html('Unexpected response from server.... contact the developer!');
        parent.find('#errors').html(returned.error);
      }

      $(button).removeClass('pulsate btn-danger btn-warning').addClass('btn-success');
      $(button).find('i').removeClass('fa-refresh fa-spin').addClass('fa-check');

      // TEMPORORY: Reload the page so the new stage can be applied
      // @todo: Make dynamic by updating points table, adding in a new stage etc
      window.location.reload();
    })
    .fail(function (xhr, textStatus, error) {
      var message = xhr.responseJSON;
      if (message && message.error) {
        parent.find('#errors').html(message.error);
      } else {
        parent.find('#errors').html("Unknown server error occured! :'(");
      }

      parent.find('#errors').slideDown();

      $(button).find('i').removeClass('fa-refresh fa-spin').addClass('fa-exclamation-triangle');
      $(button).removeClass('pulsate').addClass('btn-danger');
    });
  }
});
