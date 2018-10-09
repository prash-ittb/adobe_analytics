/**
 * @file
 * Default File.
 */
(function ($) {
  var editors = {};
  
  $(function () {
    
    
    $('.use-snippet').each(function (i, e) {
      var inputElement = $(this),
        inputId = inputElement.attr('id') + '-ace';
      
      inputElement.hide();
      
      var newEditor = $('<div class="ace-editor"></div>').attr({
        id: inputId
      }).css({
        position: 'absolute',
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }).html(inputElement.val());
      
      inputElement.after(newEditor);
      
      inputElement.parent().find('#' + inputId).wrap($('<div></div>').css({
        position: 'relative', height: '300px'
      }));
      
      var editor = ace.edit(inputId);
      editor.setTheme("ace/monokai");
      editor.getSession().setMode("ace/javascript");
      
      editors[inputId] = {
        editor: editor,
        input: inputElement
      };
      
    });
    
    $('form.ace-editors').submit(function () {
      $.each(editors, function (i, e) {
        e.input.val(editors[i].editor.getValue());
      });
    });
    
    
  });
  
})(jQuery);