<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpgr_admin_menu() {
    add_menu_page(
        'Google Recomendações',
        'Google Recomendações',
        'manage_options',
        'wpgr-admin',
        'wpgr_admin_page',
        'dashicons-star-filled',
        56
    );
}
add_action('admin_menu', 'wpgr_admin_menu');

function wpgr_admin_page() {
    ?>
    <div class="wrap">
        <h1>Configurações do Google Recomendações</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wpgr_settings_group');
            do_settings_sections('wpgr-admin');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wpgr_register_settings() {
    register_setting('wpgr_settings_group', 'wpgr_api_key');
    register_setting('wpgr_settings_group', 'wpgr_star_level');
    register_setting('wpgr_settings_group', 'wpgr_scroll_orientation');
    register_setting('wpgr_settings_group', 'wpgr_api_project_id');
    register_setting('wpgr_settings_group', 'wpgr_api_location');
    register_setting('wpgr_settings_group', 'wpgr_place_id');

    add_settings_section('wpgr_section_main', 'Configurações Principais', null, 'wpgr-admin');

    add_settings_field('wpgr_api_key', 'Chave da API do Google', 'wpgr_field_api_key', 'wpgr-admin', 'wpgr_section_main');
    add_settings_field('wpgr_api_project_id', 'ID do Projeto Google', 'wpgr_field_api_project_id', 'wpgr-admin', 'wpgr_section_main');
    add_settings_field('wpgr_api_location', 'Localização da API', 'wpgr_field_api_location', 'wpgr-admin', 'wpgr_section_main');
    add_settings_field('wpgr_place_id', 'Lugar do Google Maps', 'wpgr_field_place_id', 'wpgr-admin', 'wpgr_section_main');
    add_settings_field('wpgr_star_level', 'Nível de Estrelas', 'wpgr_field_star_level', 'wpgr-admin', 'wpgr_section_main');
    add_settings_field('wpgr_scroll_orientation', 'Orientação da Rolagem', 'wpgr_field_scroll_orientation', 'wpgr-admin', 'wpgr_section_main');
}
add_action('admin_init', 'wpgr_register_settings');

function wpgr_field_api_key() {
    $value = esc_attr(get_option('wpgr_api_key'));
    echo "<input type='text' name='wpgr_api_key' value='$value' class='regular-text' />";
}

function wpgr_field_api_project_id() {
    $value = esc_attr(get_option('wpgr_api_project_id'));
    echo "<input type='text' name='wpgr_api_project_id' value='$value' class='regular-text' />";
}

function wpgr_field_api_location() {
    $value = esc_attr(get_option('wpgr_api_location'));
    echo "<input type='text' name='wpgr_api_location' value='$value' class='regular-text' placeholder='us-central1' />";
}

function wpgr_field_star_level() {
    $value = esc_attr(get_option('wpgr_star_level', 3));
    echo "<select name='wpgr_star_level'>";
    for ($i = 1; $i <= 5; $i++) {
        $selected = ($value == $i) ? 'selected' : '';
        echo "<option value='$i' $selected>{$i} estrelas</option>";
    }
    echo "</select>";
}

function wpgr_field_scroll_orientation() {
    $value = esc_attr(get_option('wpgr_scroll_orientation', 'horizontal'));
    echo "<select name='wpgr_scroll_orientation'>
        <option value='horizontal'".($value=='horizontal'?' selected':'').">Horizontal</option>
        <option value='vertical'".($value=='vertical'?' selected':'').">Vertical</option>
    </select>";
}

function wpgr_field_place_id() {
    $place_id = esc_attr(get_option('wpgr_place_id'));
    $api_key = esc_attr(get_option('wpgr_api_key'));
    ?>
    <input id="wpgr-place-id" type="text" name="wpgr_place_id" value="<?php echo $place_id; ?>" class="regular-text" placeholder="Pesquise e selecione um lugar no Maps" autocomplete="off" />
    <div id="wpgr-map" style="width:100%; height:300px; margin-top:10px;"></div>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $api_key; ?>&libraries=places"></script>
    <script>
    (function() {
        var input = document.getElementById('wpgr-place-id');
        var mapDiv = document.getElementById('wpgr-map');
        var map, marker;
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry) return;
            if (!map) {
                map = new google.maps.Map(mapDiv, {
                    center: place.geometry.location,
                    zoom: 16
                });
                marker = new google.maps.Marker({map: map});
            }
            map.setCenter(place.geometry.location);
            marker.setPosition(place.geometry.location);
        });
        // Se já houver um place_id salvo, mostrar no mapa ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            var placeId = input.value;
            if (placeId && google.maps) {
                var service = new google.maps.places.PlacesService(mapDiv);
                service.getDetails({placeId: placeId}, function(place, status) {
                    if (status === google.maps.places.PlacesServiceStatus.OK) {
                        map = new google.maps.Map(mapDiv, {
                            center: place.geometry.location,
                            zoom: 16
                        });
                        marker = new google.maps.Marker({map: map, position: place.geometry.location});
                        input.value = place.place_id;
                    }
                });
            }
        });
    })();
    </script>
    <?php
}
