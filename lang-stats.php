<?php

/** ------------------------------------------------
 * Settings
 * ------------------------------------------------ */
# Load GitHub token. 
$github_token = file_get_contents("../.com-data/gh-key");
$username = "ubaada";
$cache_file = "../.com-cache/gh-stats-cache.json";
$cache_time = 3600; // in seconds


function fetch_top_lang($username, $token , $sizeWeight = 1, $countWeight = 0) {
    if (empty($username)) {
        throw new Exception("Missing username parameter");
    }
    #$token = getenv('GITHUB_TOKEN'); // Ensure you set this environment variable
    if (!$token) {
        throw new Exception("GitHub token not found");
    }

    $query = "
    query userInfo(\$login: String!) {
      user(login: \$login) {
        repositories(ownerAffiliations: OWNER, isFork: false, first: 100) {
          nodes {
            name
            languages(first: 10, orderBy: {field: SIZE, direction: DESC}) {
              edges {
                size
                node {
                  color
                  name
                }
              }
            }
          }
        }
      }
    }";

    $variables = ['login' => $username];

    $data = make_graphql_req($query, $variables, $token);
    
    if (isset($data['errors'])) {
        throw new Exception($data['errors'][0]['message'] ?? "An error occurred while fetching data");
    }

    $repoNodes = $data['data']['user']['repositories']['nodes'];

    /* aggregate the languages data  into individual languages */
    $languageData = [];
    foreach ($repoNodes as $repo) {
        foreach ($repo['languages']['edges'] as $lang) {
            $name = $lang['node']['name'];
            if (!isset($languageData[$name])) {
                $languageData[$name] = [
                    'name' => $name,
                    'color' => $lang['node']['color'],
                    'size' => 0,
                    'count' => 0
                ];
            }
            $languageData[$name]['size'] += $lang['size'];
            $languageData[$name]['count']++;
        }
    }
    

    /* sort the languages based on size (not count) */
    uasort($languageData, function($a, $b) {
        return $b['size'] <=> $a['size'];
    });

    return $languageData;
}


function make_graphql_req($query, $variables, $token) {
    $ch = curl_init('https://api.github.com/graphql');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode(['query' => $query, 'variables' => $variables]),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'User-Agent: PHP Script',
            'Content-Type: application/json'
        ]
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }
    curl_close($ch);

    return json_decode($response, true);
}

function create_svg($data, $text_color, $bg_color, $font_size, $font_family) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%"'
        . ' font-family="' . $font_family . '" font-size="' . $font_size . 'px">';

    $svg .= '<rect x="0" y="0" width="100%" height="100%" fill="#' . $bg_color . '"/>';

    # draw bars
    $total = array_sum(array_column($data, 'size'));
    $bar = '';
    $x = 0;
    $bar_height = 10; # px
    foreach($data as $lang) {
        $percent = ($lang['size'] / $total) * 100;
        $bar .= '<rect x="'. $x . '%" y="0" width="' . $percent . '%" height="'.$bar_height.'px" fill="' . $lang['color'] . '"/>';
        $x += $percent;
    }

    # draw language names in 2 columns below the bars
    $lang_per_col  = ceil(count($data) / 2);
    $labels = '';
    $c_rad = 5;
    $x = 0;
    $y_i = 0;
    $i = 0;
    $top_pad = 15; # padding from the top
    foreach($data as $lang) {
        # draw colour dot
        $labels .= '<svg x="' . $x . '%" y="' . ($y_i / $lang_per_col * 90) + $top_pad . '%" width="100%" height="100%">';
        $cx = $c_rad;
        $cy = $font_size / 2 + $c_rad/2;
        $labels .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $c_rad . '" fill="' . $lang['color'] . '"/>';
        $percent = round(($lang['size'] / $total) * 100, 1);
        $labels .= '<text x="15" y="' . $font_size. 'px" fill="#'
            . $text_color .'" font-size="'.$font_size.'px">' . $lang['name'] . ' ' . $percent . '%</text>';
        $labels .= '</svg>';
        if ($i == $lang_per_col - 1) {
            $x = 50;
            $y_i = 0;
        } else {
            $y_i++;
        }
        $i++;
    }

    $svg .= '<svg x="0" y="0">' . $bar . '</svg>';
    $svg .= $labels;
    return $svg . '</svg>';
}

function validate_input($input, $pattern, $default, $max_len=20) {
    if (isset($_GET[$input])) {
        $input = $_GET[$input];
    } else {
        return $default;
    }
    if (preg_match($pattern, $input) && strlen($input) <= $max_len) {
        return $input;
    } else {
        return $default;
    }
}

function get_cache($cache_file, $cache_time) {
    if (file_exists($cache_file) && time() - filemtime($cache_file) < $cache_time) {
        return json_decode(file_get_contents($cache_file), true);
    }
    return null;
}

function set_cache($cache_file, $data) {
    # check if cache directory exists and create it if it doesn't
    $cache_dir = dirname($cache_file);
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    file_put_contents($cache_file, json_encode($data));
}

// Usage example:
try {
    $topLanguages = get_cache($cache_file, $cache_time);
    if (!$topLanguages) {
        $topLanguages = fetch_top_lang($username, $github_token, 1, 0);
        set_cache($cache_file, $topLanguages);
    }
    /* Sample output:
    Array
    (
    [PHP] => Array
        (
            [name] => PHP
            [color] => #4F5D95
            [size] => 96553
            [count] => 4
        )

    [Java] => Array
        (
            [name] => Java
            [color] => #b07219
            [size] => 86234
            [count] => 2
        )
    )
    */
    # check format key is set to 'raw'
    if (isset($_GET['format']) && $_GET['format'] == 'json') {
        header('Content-Type: application/json');
        echo json_encode($topLanguages);
        exit;
    } else {
        header('Content-Type: image/svg+xml');
        $text_color = validate_input('text_color', '/^[0-9a-fA-F]{6}$/', '000000', 6);
        $bg_color = validate_input('bg_color', '/^[0-9a-fA-F]{6}$/', 'ffffff', 6);
        $font_size = validate_input('font_size', '/^\d+$/', '12', 3);
        $font_family = validate_input('font_family', '/^[a-zA-Z\s]+$/', 'Arial', 20);
        
        $svg = create_svg($topLanguages, $text_color, $bg_color, $font_size, $font_family);
        echo $svg;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}