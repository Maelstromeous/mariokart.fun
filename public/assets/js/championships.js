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

  resetBars($(this));
  setBars($(this), vehicle);
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
