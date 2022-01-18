define([
    'jquery',
    'domReady!'
], function ($) {
'use strict';
var $voiceSearchTrigger = $(".micOffImage");
var $voiceSearchMicOn = $(".onIconImage");
var $formMiniSearch = $("#search_mini_form");
var $searchInput = $("#search");
window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
if (window.SpeechRecognition) {
    $(".voice-search").removeClass("icon_mic_show");
}
function _parseTranscript(e)
{
    return Array.from(e.results).map(result => result[0]).map(result => result.transcript).join('');
}
function _transcriptHandler(e) {
    $searchInput.val(_parseTranscript(e));
    if (e.results[0].isFinal) {
        $formMiniSearch.submit();
    }
}

if (window.SpeechRecognition) {
    var recognition = new SpeechRecognition();
    recognition.interimResults = true;
    recognition.addEventListener('result', _transcriptHandler);
    recognition.addEventListener('start', _speechStart);
} else {
    $('.voice-search').hide();
}
function _speechStart() {
    console.log('BS Speech Started');
    $(".voice-search").removeClass("micOffImage");
    $(".voice-search").addClass("onIconImage");
    $(".voice-search").addClass("listening");
    $(".bs-moble-style").addClass("iconListening");
    $(".bs-moble-style").removeClass("iconMicOff");
};
function startListening(e) {
    e.preventDefault();
   e.preventDefault();
    if ($searchInput.attr("placeholder") == "Listening...") {
        recognition.stop();
        $(".voice-search").removeClass("onIconImage");
        $(".voice-search").addClass("micOffImage");
        $(".voice-search").removeClass("listening");
        $(".bs-moble-style").removeClass("iconListening");
        $(".bs-moble-style").addClass("iconMicOff");
        $searchInput.attr("placeholder", "Search for...");
    } else {
        recognition.start();
        $searchInput.attr("placeholder", "Listening...");
    }
}
jQuery(".form.minisearch label").on("click", function(){
    jQuery("#search_mini_form input#search").unbind("blur");
});

return function() {
    $voiceSearchTrigger.on('click touch', startListening);
 }
});