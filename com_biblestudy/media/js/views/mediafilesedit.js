/**
 * Handles js for mediafilesedit view. Uses JQuery
 *
 * @version     $Id: mediafilesedit.js 2025 2011-08-28 04:08:06Z genu $
 * @package     JoomlaBibleStudy
 */
$j(document).ready(function () {
    $j('#loading').ajaxStart(function () {
        $j(this).show();
    });
    $j('#loading').ajaxStop(function () {
        setTimeout("$j('#loading').hide()", 2000);
    });

    // Docman integration
    $j('#docManCategories').change(function () {
        var docManItems = $j('#docManItems');
        docManItems.removeOption(/./);
        var catId = $j('#docManCategories option:selected').attr('value');
        var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=docmanCategoryItems&format=raw&catId=';
        // request the items
        $j.ajax({
            dataType: "json",
            url: url + catId,
            success: function (data) {
                $j.each(data, function (entryIndex, entry) {
                    docManItems.addOption(entry['id'], entry['name']);
                    $j('#docManItemsContainer').show();

                    $j('#articlesCategoriesContainer').hide();
                    $j('#articlesItemsContainer').hide();

                    $j('#articleSectionCategories').removeOption(/./);
                    $j('#categoryItems').removeOption(/./);

                    $j('#articlesSections').val('');

                })
            }
        });
    });

    // Articles Integration (1.5 Version)
    $j('#articlesSections').change(function () {
        $j('#articleSectionCategories').removeOption(/./);
        var secId = $j('#articlesSections option:selected').attr('value');
        var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=articlesSectionCategories&format=raw&secId=' + secId;
        if (secId !== "") {
            $j.ajax({
                dataType: "json",
                url: url,
                success: function (data) {
                    $j.each(data, function (entryIndex, entry) {
                        $j('#articleSectionCategories').addOption(entry['id'], entry['title']);
                        $j('#articlesCategoriesContainer').show();
                        $j('#docManItemsContainer').hide();
                        $j('#docManItems').removeOption(/./);
                        $j('#docManCategories').val('');
                        $j('#virtueMartItemsContainer').hide();
                        $j('#virtueMartItems').removeOption(/./);
                        $j('#virtueMartCategories').val('');

                    });
                    refreshArticleItems();
                }
            });
            $j('#categoryItems').removeOption(/./);
        } else {
            //Hide all others
            $j('#articlesCategoriesContainer').hide();
            $j('#articlesItemsContainer').hide();
        }
    });

    $j('#articleSectionCategories')
        .change(
        function () {
            $j('#categoryItems').removeOption(/./);
            var catId = $j('#articleSectionCategories option:selected').attr('value');
            var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=articlesCategoryItems&format=raw&catId=' + catId;
            $j.ajax({
                dataType: "json",
                url: url,
                success: function (data) {
                    $j.each(data, function (entryIndex, entry) {
                        $j('#categoryItems').addOption(entry['id'], entry['title']);
                        $j('#articlesItemsContainer').show();
                    })
                }
            });
        });
    $j('#categoryItems').change(function () {
        $j('#docManItemsContainer').hide();
        $j('#docManItems').removeOption(/./);
    });

    function refreshArticleItems() {
        var catId = $j('#articleSectionCategories option:selected').attr('value');
        var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=articlesCategoryItems&format=raw&catId=' + catId;
        $j.ajax({
            dataType: "json",
            url: url,
            success: function (data) {
                $j.each(data, function (entryIndex, entry) {
                    $j('#categoryItems').addOption(entry['id'], entry['title']);
                    $j('#articlesItemsContainer').show();
                })
            }
        });
    }

    // VirtueMart integration
    $j('#virtueMartCategories').change(function () {
        var virtueMartItems = $j('#virtueMartItems');
        virtueMartItems.removeOption(/./);
        var catId = $j('#virtueMartCategories option:selected').attr('value');
        var url = 'index.php?option=com_biblestudy&controller=mediafilesedit&task=virtueMartItems&format=raw&catId=';
        // request the items
        $j.ajax({
            dataType: "json",
            url: url + catId,
            success: function (data) {
                $j.each(data, function (entryIndex, entry) {
                    virtueMartItems.addOption(entry['id'], entry['title']);
                    $j('#virtueMartItemsContainer').show();

                    $j('#articlesCategoriesContainer').hide();
                    $j('#articlesItemsContainer').hide();
                    $j('#docManItemsContainer').hide();
                    $j('#docManItems').removeOption(/./);

                    $j('#articleSectionCategories').removeOption(/./);
                    $j('#categoryItems').removeOption(/./);

                    $j('#articlesSections').val('');

                })
            }
        });
    });
    //for existing docman,  articles, and virtuemart
    $j('#docmanChange').click(function () {
        $j(this).hide();
        $j('#docMainCategoriesContainer').show();
        $j('#activeDocMan').hide();
        return false;
    });
    $j('#articleChange').click(function () {
        $j(this).hide();
        $j('#articlesSectionsContainer').show();
        $j('#activeArticle').hide();
        return false;
    });
    $j('#virtueMartChange').click(function () {
        $j(this).hide();
        $j('#virtueMartCategoriesContainer').show();
        $j('#activeVirtueMart').hide();
        return false;
    });
    $j('#articlesChange').click(function () {
        $j(this).hide();
        $j('#articlesSectionsContainer').show();
        $j('#activeArticle').hide();
        return false;
    });

    //Articles Integration (1.6 Version)
    $j('#jform_article_id').change(function () {
        var url = 'index.php?option=com_biblestudy&task=mediafilesedit.articlesCategoryItems&format=raw';
        var categories = $j("#categoryItems");
        categories.removeOption(/./); //Remove all the items before reloading them
        $j('#loadingMsg').html('Loading Articles');
        categories.ajaxAddOption(url, {catId: $j(this).val()}, false);
        categories.show();
    });
});
