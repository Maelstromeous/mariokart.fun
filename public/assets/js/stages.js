$('.new-stage select[name="track"]').change(function (event) {
  var val = $(this).val();
  var parent = $(this).parents('.new-stage');

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

$('.new-stage .save').click(function (event) {
  var data = {
    players: [],
  };
  var valid = true;
  var parent = $(this).parents('.new-stage');

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

  var options = $('.new-stage').find('.positions select');
  console.log(options);
  if (count != positions.length) {

    $(options).each(function (index, el) {
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
  $(options).each(function (index, el) {
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

  if (valid === true) {
    parent.find('#errors').slideUp();
    $(this).find('i').removeClass('fa-save').addClass('fa-refresh fa-spin');
  }
});
