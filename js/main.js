 $(document).ready(function(){
    $("#copy-button").zclip({
    path:'js/vendor/jquery/ZeroClipboard.swf',
    //copy: $.trim($("div#copy ul").text()).replace(/\s{2,}/g, '\n'), 
    copy: $.trim($("div#copy ul").text()).split("  ").join("").split("\n\n").join("\n"), 
    afterCopy:  function copied(){
                    $("p#copied").show("slow", function() {
                        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
                    });
                }
    });
});
