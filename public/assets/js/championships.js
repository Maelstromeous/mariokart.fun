$('select[name^="character-"]').change(function () {
  var size = $(this).find(':selected').attr('data-size');
  var val = $(this).val();
  var vehicleSelector = $(this).parents('.player-card').find('select[name^="vehicle-"]').first();

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
  console.log(image);
  image.attr('src', assetUrl + '/img/characters/' + val + '.jpg');
});
