/**
 * Created with JetBrains PhpStorm.
 * User: bcordis
 * Date: 9/14/13
 * Time: 11:35 AM
 * To change this template use File | Settings | File Templates.
 */
jQuery(document).ready(function () {
    jQuery(".fancybox").fancybox();

});
jQuery(".fancybox")
    .attr('rel', 'gallery')
    .fancybox({
        openEffect: 'none',
        closeEffect: 'none',
        nextEffect: 'none',
        prevEffect: 'none',
        padding: 0,
        margin: [20, 60, 20, 60] // Increase left/right margin
    });
