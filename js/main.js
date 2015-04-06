 $("#copy-button").zclip({
	path:'js/vendor/jquery/ZeroClipboard.swf',
	copy: $.trim($("div#copy ul").text()).replace(/\s{2,}/g, '\n'), 
	afterCopy: function copiado(){
					$("p#copied").show("slow");
				}
}); 
