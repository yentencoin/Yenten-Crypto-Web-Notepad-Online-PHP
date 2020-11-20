$(function(){
	
	var note = $('#note');
	
	var saveTimer,
		/*
    lineHeight = parseInt(note.css('line-height')),
		minHeight = parseInt(note.css('min-height')),
    */
    lineHeight = 18,
		minHeight = 370,    
		lastHeight = minHeight,
		newHeight = 0,
		newLines = 0;
		
	var countLinesRegex = new RegExp('\n','g');
	
	// The input event is triggered by keystrokes,
// copying and even undo / redo operations.
	
	note.on('input',function(e){
		
// Clearing the timer prevents
// save every keystroke
		clearTimeout(saveTimer);
		saveTimer = setTimeout(ajaxSaveNote, 2000);
    //saveTimer = setTimeout(ajaxSaveNote, 2000000);
		
		// Count the number of new lines
		newLines = note.val().match(countLinesRegex);
		
		if(!newLines){
			newLines = [];
		}
		
		// Increase the height of the note (if needed)
		newHeight = Math.max((newLines.length + 1)*lineHeight, minHeight);
		
		// Increase / decrease the height only once when changing
		if(newHeight != lastHeight){
			note.height(newHeight);
			lastHeight = newHeight;
		}
	}).trigger('input');	// This line will resize the note on page load
	
	function ajaxSaveNote(){
		
		// Run an AJAX POST request to save the post
		$.post('1.php', { 'note' : note.val() }, function(data){
    	//alert(data);
      getstatus = JSON.parse(data);
      //alert(getstatus.saved);
      $('#newButton').html('Save ok - '+getstatus.saved+' in: '+getstatus.time);
                });
	}
  
  
// do event handling by the save button
const newButton = document.getElementById('newButton');
function changeBackground(){
	//document.body.style.background = 'red';
  ajaxSaveNote();
}
newButton.addEventListener('click', changeBackground);
// do event handling by the save button --- END


// make a manual backup of the file
const newBackup = document.getElementById('newBackup');
function newBackupfunc(){
			$.post('1.php', { 'backup_manual' : 1 }, function(data){
          getstatus = JSON.parse(data);
          $('#newBackup').html('Backup is successful - '+getstatus.saved+' in: '+getstatus.time);
              });
}      
newBackup.addEventListener('click', newBackupfunc);
  
  
	
});
