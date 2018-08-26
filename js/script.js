jQuery(function ($) {
    $("#publish").val("Publier");
    //acf.fields.relationship.fetch();
    $('select#acf-field_5ac537c4a7642').select2()
        .on("change", function (e) {
            $("#acf-field_5ac537f2a7643 .acf-icon.-minus").trigger("click");
            $('#acf-field_5ac537f2a7643').data("organisme",$(this).val());
            $('#acf-field_5ac537f2a7643').data("paged",1);
            acf.fields.relationship.fetch();
        });

});