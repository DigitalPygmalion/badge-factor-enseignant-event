<?php while (have_posts()) : the_post(); ?>
    <?php

    the_content();

    $found = false;
    $alert = "";
    $erreur_matricule = "";
    $erreur_courriel = "";
    $erreur_motdepasse = "";

    $matricule = "";
    $courriel = "";
    $mot_de_passe = "";
    if (isset($_POST) && $_POST) {

        if (isset($_POST["matricule"]) && $_POST["matricule"]) {
            $matricule = $_POST["matricule"];
        } else {
            $alert = "Champs invalide ou obligatoire";
            $erreur_matricule = "has-error";
        }

        if (isset($_POST["courriel"]) && $_POST["courriel"]) {
            $courriel = $_POST["courriel"];
        } else {
            $alert = "Champs invalide ou obligatoire";
            $erreur_courriel = "has-error";
        }

        if (isset($_POST["password"]) && $_POST["password"]) {
            $mot_de_passe = $_POST["password"];
        } else {
            $alert = "Champs invalide ou obligatoire";
            $erreur_motdepasse = "has-error";
        }

        if ($matricule && $courriel && $mot_de_passe) {


            if (email_exists($courriel)) {
                $alert = "Compte déjà existant";
                $erreur_courriel = "has-error";
            } else {
                $inscriptions = get_post_meta(get_the_ID(), "inscriptions", true);
                foreach ($inscriptions as $inscription) {
                    if ($inscription["courriel"] == $courriel && $inscription["matricule"] == $matricule) {
                        $found = $inscription;
                        continue;
                    }
                }
            }

            if ($found) {
                $username = sanitize_title($found["prenom"] . "_" . $found["nom"]);
                $num_str = "";
                $num = 0;
                while(username_exists($username.$num_str)) {
                    $num++;
                    $num_str = "_".$num;
                }
                $username = $username . $num_str;

                $badge = get_post_meta(get_the_ID(), "badge", true);
                $organisation = get_field("organisation");

                $user_id = wp_create_user($username, $mot_de_passe, $courriel);
                wp_update_user(array('ID' => $user_id,
                    "nickname" => $found["prenom"] . " " . $found["nom"],
                    "display_name" => $found["prenom"] . " " . $found["nom"],
                    "first_name" => $found["prenom"],
                    "last_name" => $found["nom"]));
                update_user_meta($user_id, "matricule", $matricule);
                update_user_meta($user_id, "badge_organisation", array($organisation->ID));
            } else {
                $erreur_matricule = "has-error";
                $erreur_courriel = "has-error";
                $alert = "Information non trouvé";
            }
        }
    }
    if ($alert) {
        ?>
        <div class="alert alert-danger"><?php echo $alert ?></div>
    <?php } ?>
    <?php if (!$found) { ?>
        <form method="post" action="">
            <input type="hidden" value="inscription_invitation_badge" name="action"/>
            <div class="form-group <?php echo $erreur_matricule ?>">
                <label class="control-label" for="matricule">Matricule</label>
                <input type="text" id="matricule" name="matricule" value="<?php echo $matricule ?>"
                       class="form-control">
            </div>
            <div class="form-group <?php echo $erreur_courriel ?>">
                <label class="control-label" for="courriel">Courriel</label>
                <input type="email" id="courriel" name="courriel" value="<?php echo $courriel ?>" class="form-control">
            </div>
            <div class="form-group <?php echo $erreur_motdepasse ?>">
                <label class="control-label" for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Soumettre</button>
        </form>
    <?php } else {
        ?>
        <div class="alert alert-success">Votre compte a été créé.</div>
        <?php
    } ?>
<?php endwhile; ?>