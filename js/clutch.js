(function ($) {
	$(document).ready(function(){

    //Disable and gray out the selections in other sections

    if ($('.new-bundles .form-checkbox').length < 1){
      $('.new-bundles .form-submit').prop('disabled', true);
      $('.new-bundles').addClass('disabled');
    }
    if ($('.delete-bundles .form-checkbox').length < 1){
      $('.delete-bundles .form-submit').prop('disabled', true);
      $('.delete-bundles').addClass('disabled');
    }
    if ($('.update-bundles .form-checkbox').length < 1){
      $('.update-bundles .form-submit').prop('disabled', true);
      $('.update-bundles').addClass('disabled');
    }
    $('.new-bundles .form-checkbox').click(function(){
      if (this.checked) {
        $('.delete-bundles .form-checkbox').prop('disabled', true);
        $('.delete-bundles .form-submit').prop('disabled', true);
        $('.delete-bundles').addClass('disabled');
        $('.update-bundles .form-checkbox').prop('disabled', true);
        $('.update-bundles .form-submit').prop('disabled', true);
        $('.update-bundles').addClass('disabled');
      } else {
        $('.delete-bundles .form-checkbox').removeAttr('disabled');
        $('.delete-bundles .form-submit').removeAttr('disabled');
        $('.delete-bundles').removeClass('disabled');
        $('.update-bundles .form-checkbox').removeAttr('disabled');
        $('.update-bundles .form-submit').removeAttr('disabled');
        $('.update-bundles').removeClass('disabled');
        if ($('.update-bundles .form-checkbox').length < 1){
          $('.update-bundles .form-submit').prop('disabled', true);
          $('.update-bundles').addClass('disabled');
        }
        if ($('.delete-bundles .form-checkbox').length < 1){
          $('.delete-bundles .form-submit').prop('disabled', true);
          $('.delete-bundles').addClass('disabled');
        }
        if ($('.new-bundles .form-checkbox').length < 1){
          $('.new-bundles .form-submit').prop('disabled', true);
          $('.new-bundles').addClass('disabled');
        }
      }
    })
    $('.delete-bundles .form-checkbox').click(function(){
      if (this.checked) {
        $('.new-bundles .form-checkbox').prop('disabled', true);
        $('.new-bundles .form-submit').prop('disabled', true);
        $('.new-bundles').addClass('disabled');
        $('.update-bundles .form-checkbox').prop('disabled', true);
        $('.update-bundles .form-submit').prop('disabled', true);
        $('.update-bundles').addClass('disabled');
      } else {
        $('.new-bundles .form-checkbox').removeAttr('disabled');
        $('.new-bundles .form-submit').removeAttr('disabled');
        $('.new-bundles').removeClass('disabled');
        $('.update-bundles .form-checkbox').removeAttr('disabled');
        $('.update-bundles .form-submit').removeAttr('disabled');
        $('.update-bundles').removeClass('disabled');
        if ($('.update-bundles .form-checkbox').length < 1){
          $('.update-bundles .form-submit').prop('disabled', true);
          $('.update-bundles').addClass('disabled');
        }
        if ($('.delete-bundles .form-checkbox').length < 1){
          $('.delete-bundles .form-submit').prop('disabled', true);
          $('.delete-bundles').addClass('disabled');
        }
        if ($('.new-bundles .form-checkbox').length < 1){
          $('.new-bundles .form-submit').prop('disabled', true);
          $('.new-bundles').addClass('disabled');
        }
      }
    })
    $('.update-bundles .form-checkbox').click(function(){
      if (this.checked) {
        $('.delete-bundles .form-checkbox').prop('disabled', true);
        $('.delete-bundles .form-submit').prop('disabled', true);
        $('.delete-bundles').addClass('disabled');
        $('.new-bundles .form-checkbox').prop('disabled', true);
        $('.new-bundles .form-submit').prop('disabled', true);
        $('.new-bundles').addClass('disabled');
      } else {
        $('.new-bundles .form-checkbox').removeAttr('disabled');
        $('.new-bundles .form-submit').removeAttr('disabled');
        $('.new-bundles').removeClass('disabled');
        $('.delete-bundles .form-checkbox').removeAttr('disabled');
        $('.delete-bundles .form-submit').removeAttr('disabled');
        $('.delete-bundles').removeClass('disabled');
        if ($('.update-bundles .form-checkbox').length < 1){
          $('.update-bundles .form-submit').prop('disabled', true);
          $('.update-bundles').addClass('disabled');
        }
        if ($('.delete-bundles .form-checkbox').length < 1){
          $('.delete-bundles .form-submit').prop('disabled', true);
          $('.delete-bundles').addClass('disabled');
        }
        if ($('.new-bundles .form-checkbox').length < 1){
          $('.new-bundles .form-submit').prop('disabled', true);
          $('.new-bundles').addClass('disabled');
        }
      }
    })
	});

  //Select all checkbox
  $("#edit-new-bundles-select-all").change(function () {
    $(".new-bundles input:checkbox").prop('checked', $(this).prop("checked"));
  });

  $("#edit-delete-bundles-select-all").change(function () {
    $(".delete-bundles input:checkbox").prop('checked', $(this).prop("checked"));
  });

  $("#edit-update-bundles-select-all").change(function () {
    $(".update-bundles input:checkbox").prop('checked', $(this).prop("checked"));
  });

})(jQuery);
