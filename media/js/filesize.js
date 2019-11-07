function openConverter1() {
	var Wheight = 170;
	var Wwidth = 300;
	var winl = (screen.width - Wwidth) / 2;
	var wint = (screen.height - Wheight) / 2;

	var msg1 = window.open("components/com_biblestudy/convert1.htm", "Window", "scrollbars=1,width=" + Wwidth + ",height=" + Wheight + ",top=" + wint + ",left=" + winl);
	if (!msg1.closed) {
		msg1.focus();
	}
}
