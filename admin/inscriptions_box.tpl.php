<style>
    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }

    .table-bordered {
        border: 1px solid #ddd;
    }

    .table-bordered tbody tr td,
    .table-bordered tbody tr th,
    .table-bordered tfoot tr td,
    .table-bordered tfoot tr th,
    .table-bordered thead tr td,
    .table-bordered thead tr th {
        border: 1px solid #ddd;
    }

    .table thead tr th {
        vertical-align: bottom;
        border-bottom: 2px solid #ddd;
    }

    .table tr td,
    .table tr th {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }

    .table caption + thead tr:first-child td,
    .table caption + thead tr:first-child th,
    .table colgroup + thead tr:first-child td,
    .table colgroup + thead tr:first-child th,
    .table thead:first-child tr:first-child td,
    .table thead:first-child tr:first-child th {
        border-top: 0;
    }

    .table-bordered thead tr td,
    .table-bordered thead tr th {
        border-bottom-width: 2px;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #f9f9f9;
    }
</style>
<?php

$inscriptions = get_post_meta(get_the_ID(), "inscriptions", true);
$inscriptions_user = array();
$inscriptions_non = array();
$badges = get_field("badges");

if ($inscriptions) {
    foreach ($inscriptions as $inscription) {
        if ($user_id = email_exists($inscription["courriel"])) {
            $user = get_user_by("ID", $user_id);
            $inscription["user"] = $user;
            $inscriptions_user[] = $inscription;
        } else {
            $inscriptions_non[] = $inscription;
        }
    }

    ?>
    <h3>Organisation : <b><?php echo get_field("organisation")->post_title; ?></b></h3>

    <?php if ($inscriptions_user) { ?>
        <div class="table table-striped">
            <table width="100%" cellpadding="5" cellspacing="0">
                <thead>
                <tr>
                    <th valign="bottom">
                        <h3>Liste des utilisateurs inscrits</h3></th>
                    <?php foreach ($badges as $badge) {
                        ?>
                        <th valign="bottom" width="200px">
                            <?php echo get_the_post_thumbnail($badge->ID, 'medium', array("style" => "width:25px; height: auto")) ?>
                            <br/>
                            <?php echo $badge->post_title; ?></th>
                        <?php
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($inscriptions_user as $inscription) {
                    ?>
                    <tr>
                        <td><?php echo $inscription["nom"] . " " . $inscription["prenom"] . " (" . $inscription["matricule"] . ")" ?></td>
                        <?php foreach ($badges as $badge) {
                            $nomination = $GLOBALS['badgefactor']->get_submission($badge->ID, $inscription["user"]);
                            $statut = "";
                            if ($nomination)
                                $statut = get_post_meta($nomination->ID, '_badgeos_nomination_status', true);
                            ?>
                            <td align="center">
                                <?php

                                if (!isset($nomination) || ($statut != "approved")) { ?>
                                    <button type="button" class="button btn-approuver"
                                            data-user-id="<?php echo $inscription["user"]->ID ?>"
                                            data-id="<?php echo $badge->ID ?>">
                                        Approuver
                                    </button>
                                    <?php
                                } else {
                                    ?>
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php
                                }
                                ?>
                            </td>
                            <?php
                        }
                        ?>
                    </tr>
                    <?php
                } ?>

                </tbody>
            </table>
        </div>
    <?php } ?>


    <?php if ($inscriptions_non) { ?>

        <hr/>
        <h3>Liste des invitations non inscrites</h3>
        <div class="table table-striped table-bordered">
            <table width="100%" cellpadding="5" cellspacing="0">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Matricule</th>
                    <th>Courriel</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($inscriptions_non as $inscription) {
                    ?>
                    <tr>
                        <td><?php echo $inscription["nom"] ?></td>
                        <td><?php echo $inscription["prenom"] ?></td>
                        <td><?php echo $inscription["matricule"] ?></td>
                        <td><?php echo $inscription["courriel"] ?></td>
                    </tr>
                    <?php
                } ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}


?>
<script>
    jQuery(function ($) {
        $(".btn-approuver").bind('click', function (event) {

            event.preventDefault();
            var $button = $(this);
            $button.attr("disabled", "disabled");

            $.ajax({
                method: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'inscrire_badge_utilisateur',
                    'badge_id': $button.data('id'),
                    'user_id': $button.data('user-id'),
                },
                dataType: 'json',
                success: function (response) {
                    $button.parent().append("<span class=\"dashicons dashicons-yes\"></span>");
                    $button.remove();
                }
            });

        });
    });
</script>
