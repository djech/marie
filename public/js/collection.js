$(document).ready(function() {

    setEmbedForm($('div#information_prestations_prestations'), 'prestation');
    setEmbedForm($('div#information_pathologies_pathologies'), 'pathologie');

    /**
     * Prépare un champ de type Collection (ajout bouton d'ajout, de suppression et ajout titre)
     */
    function setEmbedForm($selector, fieldName, subSelectorArray) {
        addAddLink($selector, fieldName, subSelectorArray);
        $selector.children('div.box').each(function() {
            addTitle($(this));
            addDelLink($(this));
        });
    }

    /**
     * Ajoute le lien d'ajout d'un nouveau formulaire
     */
    function addAddLink($selector, fieldName, subSelectorArray) {
        var $addLink = $('<a href="#" class="btn btn-sm btn-success"><i class="fa fa-plus"></i>&nbsp;Ajouter ' + fieldName + '</a>');
        $selector.append($addLink);


        $addLink.on('click', function(e) {
            e.preventDefault();
            addForm($selector);
            addTitle($selector.children('div.box').last());
            addDelLink($selector.children('div.box').last());

            for (var key in subSelectorArray) {
                var $value = subSelectorArray[key];
                addAddLink($selector.find($value.selector).last(), key);
            }
        });
    }

    /**
     * Ajoute le lien de suppression
     */
    function addDelLink($selector) {
        var $removeLink = $('<div style="margin: 3px;"><a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i>&nbsp;Retirer</div>');
        $selector.append($removeLink);

        $removeLink.on('click', function(e) {
            e.preventDefault();
            $selector.remove();
        });
    }

    /**
     * Ajoute le titre
     */
    function addTitle($selector) {
        var title = $selector.find('input').first().val();
        if (title == '') {
            title = 'Non enregistré(e)';
        }
        $selector.children('div.box-header').children('div.box-header-name').html(title);
    }

    /**
     * Ajoute le nouveau formulaire
     */
    function addForm($selector) {
        $selector.data('index', $selector.find(':input').length);

        var prototype = $selector.data('prototype');
        var index = $selector.data('index');

        var fieldName = getFieldNameFromSelector($selector.attr('id'));
        var prototype_name = '__' + fieldName + '__';

        var $newForm = $(prototype.replace(new RegExp(prototype_name, 'g'), index));

        $newForm.find('div.box-header i').removeClass('fa-plus').addClass('fa-minus');
        $newForm.removeClass('collapsed-box');

        $selector.children('.btn-success').before($newForm);
    }

    /**
     * Décortique le nom du champ depuis le selecteur
     */
    function getFieldNameFromSelector(selector) {
        var split = selector.split('_');

        return split[split.length - 1];
    }

});