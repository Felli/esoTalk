var BBCode = {

bold: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[b]", "[/b]");},
italic: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[i]", "[/i]");},
underline: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[u]", "[/u]");},
strikethrough: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[s]", "[/s]");},
subscript: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[sub]", "[/sub]");},
superscript: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[sup]", "[/sup]");},
header: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[h]", "[/h]");},
center: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[center]", "[/center]");},
link: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[url=http://example.com]", "[/url]", "http://example.com", "link text");},
image: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[img]", "[/img]", "", "http://example.com/image.jpg");},
fixed: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[code]", "[/code]");},
textcolor: function(id) {ETConversation.wrapText($("#"+id+" textarea"), "[color=darkred]", "[/color]", "darkred", "Color Text");},

};
$(document).ready(function() {
	$.SyntaxHighlighter.init({'debug': true,'alternateLines': true,'prettifyBaseUrl':ET.webPath+'/plugins/BBCode/resources/jquery-syntaxhighlighter/prettify','baseUrl':ET.webPath+'/plugins/BBCode/resources/jquery-syntaxhighlighter'});
});
