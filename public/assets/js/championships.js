$('select[name^="character-"]').change(function () {
  var size = $(this).find(':selected').attr('data-size');
  var val = $(this).val();
  var vehicleSelector = $(this).parents('.player-card').find('select[name^="vehicle-"]').first();
  resetBars($(this));

  // Reset options
  vehicleSelector.find('option[data-size="' + size + '"]').prop('disabled', false).show();
  vehicleSelector.val(0);
  vehicleSelector.prop('disabled', true);

  if (size) {
    vehicleSelector.prop('disabled', false);
    vehicleSelector.find('option[data-size!="' + size + '"]').prop('disabled', true).hide();
  }

  // Pull in image
  var image = $(this).parents('.player-card').find('img.character').first();
  if (val > 0) {
    image.attr('src', assetUrl + '/img/characters/' + val + '.jpg' + version);
  } else {
    image.attr('src', assetUrl + '/img/GoldenMushie-Faded.png');
  }
});

$('select[name^="vehicle-"]').change(function () {
  var vehicle = $(this).val();

  // Reset any validation error colourings
  $(this).css('background-color', '');

  if (vehicle == '0') {
    resetBars($(this));
  }

  setBars($(this), vehicle);
});

$('select[name^="player-"]').change(function () {
  // Reset any validation error colourings
  $(this).css('background-color', '');

  var data = { id: $(this).val() };
  var $elem = $(this);

  // Grab players defaults and apply them if present
  $.ajax({
    url: baseUrl + '/misc/player-defaults',
    type: 'POST',
    dataType: 'json',
    data: JSON.stringify(data),
  })
  .done(function (returned) {
    console.log(returned);

    // If we have the values, set them
    if (returned.character) {
      $elem.parents('.player-card')
        .find('select[name^="character-"]')
        .val(returned.character)
        .trigger('change');
    }

    if (returned.vehicle) {
      $elem.parents('.player-card')
        .find('select[name^="vehicle-"]')
        .val(returned.vehicle)
        .trigger('change');
    }
  })
  .fail(function (xhr, textStatus, error) {

  });
});

function resetBars(elem) {
  var bars = $(elem).parents('.player-card').find('.progress-bar');
  $(bars).each(function (index, el) {
    $(this).css('width', '0px');
    $(this).html('');
  });
}

function setBars(elem, vehicle) {
  var data = vehicleStats[vehicle];
  var bars = $(elem).parents('.player-card').find('.progress-bar');

  $(bars).each(function (index, el) {
    var prop = $(this).attr('data-prop');
    var min = $(this).attr('data-min') - 1; // - 2 so we can see the edge
    var max = $(this).attr('data-max');
    var stat = parseInt(vehicleStats[vehicle][prop]);

    // Colour bars depending on good or bad
    $(this).removeClass('bg-success bg-danger');
    $(this).css('color', '#fff');

    /* Calculate proper 100% width as each propery has a different maximum...
       although on the wiki it says 80? ¯\_(ツ)_/¯ */

    var statmod = stat - min;
    var maxmod = max - min;
    var width = (statmod / maxmod) * 100;

    // console.log(`(${statmod} / ${maxmod}) * 100 = ${width}`);

    $(this).css('width', width.toFixed(2) + '%');
    $(this).html(stat);

    if (prop === 'weight') {
      if (width < 25) {
        $(this).addClass('bg-success');
      } else if (width > 75) {
        $(this).addClass('bg-danger');
      }
    } else {
      if (width > 75) {
        $(this).addClass('bg-success');
      } else if (width < 25) {
        $(this).addClass('bg-danger');
      }
    }

    if (width < 15) {
      $(this).css('color', '#000');
    }
  });
}

$('#submit').click(function () {
  $('#form-errors').html('').hide();
  var data = {
    players: [],
  };
  var valid = true;

  // Check for valid data and populate params if so
  $('.player-card').each(function (index, el) {
    console.log(el);
    var id = $(el).attr('data-id');
    var character = $(el).find('select[name^="character-"]').val();
    var vehicle = $(el).find('select[name^="vehicle-"]').val();
    var player = $(el).find('select[name^="player-"]').val();

    if (!character || character == 0) {
      return false;
    }

    if (!vehicle || vehicle == 0) {
      $('#form-errors').html('A character is missing a vehicle. ' +
        'Please correct and try again.').show();
      $(el).find('select[name^="vehicle-"]').css('background-color', '#ff8585');
      valid = false;
      return false;
    }

    if (!player || player == 0) {
      $('#form-errors').html('A character is missing the player. ' +
        'Please correct and try again.').show();
      $(el).find('select[name^="player-"]').css('background-color', '#ff8585');
      valid = false;
      return false;
    }

    var obj = {
      character: character,
      vehicle: vehicle,
      player: player,
    };

    data.players.push(obj);
  });

  if (!valid) {
    return false;
  }

  if (data.players.length === 0) {
    alert('No data to submit!');
    return false;
  }

  $('#submit i').removeClass('fa-arrow-right').addClass('fa-refresh fa-spin');

  // Now we've passed validation and we have data to send
  $.ajax({
    url: baseUrl + '/championships/new',
    type: 'POST',
    dataType: 'json',
    data: JSON.stringify(data),
  })
  .done(function (returned) {
    $('#submit i').removeClass('fa-refresh fa-spin').addClass('fa-check');
    window.location.replace(baseUrl + '/championship/' + returned.id);
  })
  .fail(function (xhr, textStatus, error) {
    console.log('xhr', xhr);
    console.log('error', error);
    $('#form-errors').html('A server error occured: ' + error).show();
    $('#submit i').removeClass('fa-refresh fa-spin').addClass('fa-arrow-right');
  });
});
