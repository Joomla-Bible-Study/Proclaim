const $j = jQuery.noConflict()

$(function () {
    setDefaults()
    let files

    $j('#dirStatus').ajaxStart(function () {
        $j(this).html('Searching...')
    })
    $j('input[name=directoryname]').on('blur',function () {
        getFolder($j(this).val())
    })

    getFolder($j('input[name=directoryname]').val())

    //Helper Functions
    function getFolder(folder)
    {
        $j.getJSON(
            'index.php',
            {
                option: 'com_proclaimimport',
                controller: 'ajax',
                format: 'raw',
                task: 'getFolder',
                folder: folder,
            },
            function (json) {
                if (json == null) {
                    $j('#dirStatus').html('invalid folder')
                    return
                }
                $j('#dirStatus').html('Files: ' + json.fileCount)
            },
        )
    }

    $j('.viewFile').on('click',function () {
        eval('var viewFile = ' + $j(this).attr('name'))
        //alert(viewFile.filename);
        return false
    })

    //Global move files
    $j('#moveFiles').on('mouseup',function () {
        $j('.moveFile').attr('checked', !$j(this).attr('checked'))
    })
    //Global Custom Text
    $j('.globalCustom').on('change',function () {
        const field = $j(this).attr('name')
        const newValue = $j(this).val()
        $j('.availableTags[name=' + field + ']').val('- Use element from ID3 -')
        $j('.existing[name=' + field + ']').val('- Use existing data -')
        $j('.' + field).val(newValue)

    })

    //Global Id3 Information
    $j('.availableTags').on('change',function () {
        const field = $j(this).attr('name')
        const jsonField = $j(this).val()
        $j('.globalCustom[name=' + field + ']').val('')
        $j('.existing[name=' + field + ']').val('- Use existing data -')
        $j('.' + field).val(function () {
            const newValue = ''
            try {
                eval(
                    '' + $j(this).attr('alt') + '.' + jsonField + ';'
                )
            } catch (e) {
            }
            return newValue
        })
    })

    //Global Existing Information
    $j('.existing').on('change',function () {
        const field = $j(this).attr('name')
        $j('.globalCustom[name=' + field + ']').val('')
        $j('.availableTags[name=' + field + ']').val('- Use element from ID3 -')
        $j('.' + field).val($j(this).find(':selected').text())
    })

    //Start the import
    $j('#import').on('click',function () {
        const json = $j('#files tr:last').find('input').serializeArray()
        $j.ajax(
            {
                type: 'POST',
                url: 'index.php?option=com_proclaim&controller=ajax&format=raw&task=importFile',
                data: json,
                success: function (response) {
                },
            },
        )

        //});
        return false
    })

    function setDefaults()
    {
        $j('.availableTags').each(function () {
            const field = $j(this).attr('name')
            const jsonField = $j(this).val()

            $j('.' + field).val(function () {
                const newValue = ''
                try {
                    eval('' + $j(this).attr('alt') + '.' + jsonField + '')
                } catch (e) {
                }
                return newValue
            })
        })
    }
})
