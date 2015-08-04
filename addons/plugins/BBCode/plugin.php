<?php
// Copyright 2011 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

ET::$pluginInfo["BBCode"] = array(
	"name" => "BBCode",
	"description" => "Formats BBCode within posts, allowing users to style their text.",
	"version" => ESOTALK_VERSION,
	"author" => "esoTalk Team",
	"authorEmail" => "support@esotalk.org",
	"authorURL" => "http://esotalk.org",
	"license" => "GPLv2"
);


/**
 * BBCode Formatter Plugin
 *
 * Interprets BBCode in posts and converts it to HTML formatting when rendered. Also adds BBCode formatting
 * buttons to the post editing/reply area.
 */
class ETPlugin_BBCode extends ETPlugin {


/**
 * Add an event handler to the initialization of the conversation controller to add BBCode CSS and JavaScript
 * resources.
 *
 * @return void
 */
public function handler_conversationController_renderBefore($sender)
{
	$sender->addJSFile($this->resource("jquery-syntaxhighlighter/scripts/jquery.syntaxhighlighter.min.js"));
	$sender->addJSFile($this->resource("bbcode.js"));
	$sender->addCSSFile($this->resource("bbcode.css"));
}


/**
 * Add an event handler to the "getEditControls" method of the conversation controller to add BBCode
 * formatting buttons to the edit controls.
 *
 * @return void
 */
public function handler_conversationController_getEditControls($sender, &$controls, $id)
{
	addToArrayString($controls, "fixed", "<a href='javascript:BBCode.fixed(\"$id\");void(0)' title='".T("Fixed")."' class='bbcode-fixed'><span>".T("Fixed")."</span></a>", 0);
	addToArrayString($controls, "image", "<a href='javascript:BBCode.image(\"$id\");void(0)' title='".T("Image")."' class='bbcode-img'><span>".T("Image")."</span></a>", 0);
	addToArrayString($controls, "link", "<a href='javascript:BBCode.link(\"$id\");void(0)' title='".T("Link")."' class='bbcode-link'><span>".T("Link")."</span></a>", 0);
	addToArrayString($controls, "header", "<a href='javascript:BBCode.header(\"$id\");void(0)' title='".T("Header")."' class='bbcode-h'><span>".T("Header")."</span></a>", 0);
	addToArrayString($controls, "center", "<a href='javascript:BBCode.center(\"$id\");void(0)' title='".T("Center")."' class='bbcode-center'><span>".T("Center")."</span></a>", 0);
	addToArrayString($controls, "subscript", "<a href='javascript:BBCode.subscript(\"$id\");void(0)' title='".T("Subscript")."' class='bbcode-sub'><span>".T("Subscript")."</span></a>", 0);
	addToArrayString($controls, "superscript", "<a href='javascript:BBCode.superscript(\"$id\");void(0)' title='".T("Superscript")."' class='bbcode-sup'><span>".T("Superscript")."</span></a>", 0);
	addToArrayString($controls, "strike", "<a href='javascript:BBCode.strikethrough(\"$id\");void(0)' title='".T("Strike")."' 
class='bbcode-s'><span>".T("Strike")."</span></a>", 0);
	addToArrayString($controls, "underline", "<a href='javascript:BBCode.underline(\"$id\");void(0)' title='".T("Underline")."' class='bbcode-u'><span>".T("Underline")."</span></a>", 0);
	addToArrayString($controls, "italic", "<a href='javascript:BBCode.italic(\"$id\");void(0)' title='".T("Italic")."' class='bbcode-i'><span>".T("Italic")."</span></a>", 0);
	addToArrayString($controls, "bold", "<a href='javascript:BBCode.bold(\"$id\");void(0)' title='".T("Bold")."' class='bbcode-b'><span>".T("Bold")."</span></a>", 0);
	addToArrayString($controls, "textcolor", "<a href='javascript:BBCode.textcolor(\"$id\");void(0)' title='".T("Color")."' class='control-textcolor'><i class='icon-tint'></i></a>", 0);

}


/**
 * Add an event handler to the formatter to take out and store code blocks before formatting takes place.
 *
 * @return void
 */
public function handler_format_beforeFormat($sender)
{
	$this->blockFixedContents = array();
	$this->inlineFixedContents = array();
	$self = $this;

	$regexp = "/(.*)^\s*\[code\]\n?(.*?)\n?\[\/code]$/ims";
	while (preg_match($regexp, $sender->content)) {
		if ($sender->inline) {
			$sender->content = preg_replace_callback($regexp, function ($matches) use ($self) {
				$self->inlineFixedContents[] = $matches[2];
				return $matches[1].'<code></code>';
			}, $sender->content);
		} else {
			$sender->content = preg_replace_callback($regexp, function ($matches) use ($self) {
				$self->blockFixedContents[] = $matches[2];
				return $matches[1].'</p><pre></pre><p>';
			}, $sender->content);
		}
	}

	// Inline-level [fixed] tags will become <code>.
	$sender->content = preg_replace_callback("/\[code\]\n?(.*?)\n?\[\/code]/is", function ($matches) use ($self) {
		$self->inlineFixedContents[] = $matches[1];
		return '<code></code>';
	}, $sender->content);
}


/**
 * Add an event handler to the formatter to parse BBCode and format it into HTML.
 *
 * @return void
 */
public function handler_format_format($sender)
{
	// TODO: Rewrite BBCode parser to use the method found here:
	// http://stackoverflow.com/questions/1799454/is-there-a-solid-bb-code-parser-for-php-that-doesnt-have-any-dependancies/1799788#1799788
	// Remove control characters from the post.
	//$sender->content = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $sender->content);
	// \[ (i|b|color|url|somethingelse) \=? ([^]]+)? \] (?: ([^]]*) \[\/\1\] )

	// Images: [img]url[/img]
	if (!$sender->basic) $sender->content = preg_replace("/\[img\](.*?)\[\/img\]/i", "<img src='$1' alt='-image-'/>", $sender->content);

	// Links with display text: [url=http://url]text[/url]
	$sender->content = preg_replace("/\[url=(\w{2,6}:\/\/)?([^\]]*?)\](.*?)\[\/url\]/ie", "'<a href=\'' . ('$1' ? '$1' : 'http://') . '$2\' rel=\'nofollow external\' target=\'_blank\'>$3</a>'", $sender->content);

	// Bold: [b]bold text[/b]
	$sender->content = preg_replace("|\[b\](.*?)\[/b\]|si", "<b>$1</b>", $sender->content);

	// Italics: [i]italic text[/i]
	$sender->content = preg_replace("/\[i\](.*?)\[\/i\]/si", "<i>$1</i>", $sender->content);
	
	// Underline: [u]underlined text[/u]
	$sender->content = preg_replace("/\[u\](.*?)\[\/u\]/si", "<u>$1</u>", $sender->content);

	// Strikethrough: [s]strikethrough[/s]
	$sender->content = preg_replace("/\[s\](.*?)\[\/s\]/si", "<del>$1</del>", $sender->content);
	
	// Superscript: [sup]superscript[/sup]
	$sender->content = preg_replace("/\[sup\](.*?)\[\/sup\]/si", "<sup>$1</sup>", $sender->content);
	
	// Subscript: [sub]subscript[/sub]
	$sender->content = preg_replace("/\[sub\](.*?)\[\/sub\]/si", "<sub>$1</sub>", $sender->content);
	
	// Center: [center]center text[/center]
	$sender->content = preg_replace("|\[center\](.*?)\[/center\]|si", "<center>$1</center>", $sender->content);

	// Headers: [h]header[/h]
	$sender->content = preg_replace("/\[h\](.*?)\[\/h\]/", "</p><h4>$1</h4><p>", $sender->content);
	
	// Font Color: [color=$1]text[/color]
        $sender->content = preg_replace("/\[color=([#a-z0-9]+)\](.*?)\[\/color\]/is", "<span style=\"color:\\1\">\\2</span>", $sender->content);
}


/**
 * The callback function used to replace URL BBCode with HTML anchor tags.
 *
 * @param array $matches An array of matches from the regular expression.
 * @return string The replacement HTML anchor tag.
 */
public function linksCallback($matches)
{
	return ET::formatter()->formatLink($matches[1].$matches[2], $matches[3]);
}


/**
 * Add an event handler to the formatter to put code blocks back in after formatting has taken place.
 *
 * @return void
 */
public function handler_format_afterFormat($sender)
{
	$self = $this;

	// Retrieve the contents of the inline <code> tags from the array in which they are stored.
	$sender->content = preg_replace_callback("/<code><\/code>/i", function ($matches) use ($self) {
		return '<code>'.array_shift($self->inlineFixedContents).'</code>';
	}, $sender->content);

	// Retrieve the contents of the block <pre> tags from the array in which they are stored.
	if (!$sender->inline) {
		$sender->content = preg_replace_callback("/<pre><\/pre>/i", function ($matches) use ($self) {
			return '<pre>'.array_pop($self->blockFixedContents).'</pre>';
		}, $sender->content);
	}
}

}
