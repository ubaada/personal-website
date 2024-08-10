<!DOCTYPE html>
<html>

<head>
    <!-- Tags which *must* come first in the header -->
    <meta charset="utf-8">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        Ubaada
    </title>
    <!-- Facebook preview card -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Ubaada | Dev" />
    <meta property="og:description" content="Software engineer based in New Zealand" />
    <meta property="og:image" content="https://www.ubaada.com/images/me.jpg" />

    <!-- Twitter preview card --->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@ubaada">
    <meta name="twitter:creator" content="@ubaada">
    <meta name="twitter:title" content="Ubaada | Dev">
    <meta name="twitter:description" content="Software engineer based in New Zealand.">
    <meta name="twitter:image" content="https://www.ubaada.com/images/me.jpg">

    <!-- Description for search results-->
    <meta name="description" content="Software engineer based in New Zealand.">

    <link rel="icon" type="image/x-icon" href="images/favicon.png">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link href="css/common.css" rel="stylesheet">

    <!-- color-mode JS -->
    <script src="js/color-mode.js"></script>

    <!-- Google Analytics, optimised loading -->
    <script>
    // only load google analytics if not in dev mode
    // dev mode: 
    //     either on localhost (1)
    //     dev=true in localstorage or ?dev=true in url (2)

    var isDev = false;

    // check if hostname contains localhost (1)
    isDev = location.hostname.includes("localhost");
    if (isDev === false) {
        // check if (true|false) dev is in localstorage (2)
        var isDev = localStorage.getItem("dev") !== null;
        if (!isDev) {
            // dev not in localstorage, check if dev=true in url (2)
            var urlSearchParams = new URLSearchParams(window.location.search);
            var params = Object.fromEntries(urlSearchParams.entries());

            // if dev=true in url, set dev=true in localstorage for future use
            if (params.dev) {
                localStorage.setItem("dev", "yes");
                isDev = true;
            }
        }
    }

    // if not in dev mode, load google analytics
    if (isDev === false) {
        var head = document.getElementsByTagName("head")[0];
        var adScript = document.createElement("script");
        adScript.type = "text/javascript";
        var gaID = "G-6YFPNXLP2B";
        adScript.src = "https://www.googletagmanager.com/gtag/js?id=" + gaID;
        adScript.async = true;
        head.appendChild(adScript);

        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', gaID);
    } else {
        // dev mode, load google analytics
        console.log("dev mode");
    }
    </script>

    <style>

    html,
    body {
        margin: 0;
        padding: 0;
        font-family: 'CascadiaCode', monospace;
        font-weight: 300;
        -webkit-print-color-adjust: exact;
    }

    body {}

    .container {
        /* mobile first, centred, width 90% */
        /* margin: 0 auto; */
        width: 90%;
        padding-top: 20px;
        max-width: 800px;
    }

    .my-image {
        width: 400px;
        max-width: 100%;
    }

    #about-text{
        margin-top: 20px;
    }

    a {
        text-decoration: none;
    }

    p {}

    .title-bar {
        display: none;
    }

    .window-content {
        padding: 10px;
    }

    #projects, #posts, #gh-stats-container {
        margin-top: 70px;
    }
    #projects>p {
        text-align: justify;
    }
    #gh-stats {
        margin-top: 20px;
    }
    #gs-bar-container {
        display: flex;
        gap: 0.3em;
    }
    #gs-bar-container .gs-bar {
        height: 10px;
    }
    #gs-all-labels {
        display: flex;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    .gs-label-box {
        display: flex;
        gap: 1em;
        align-items: center;
        width: 50%;
    }
    .gs-label-dot {
        width: 10px;
        height: 10px;
    }
    .gs-label-text {
        font-size: 0.8em;
    }
    
    #foot {
        display: flex;
        margin: 70px 0 50px 0;
        color: var(--footer-txt-color);
        font-size: 0.8em;
        align-items: center;
        gap: 1em;
    }

    #last-mod {
    }

    /*when tablet or larger and print*/
    @media print, screen and (min-width: 800px) {
        .window {
            /* border: 1px solid black;  */
            margin: 20px auto;
            max-width: 800px;
            border-radius: 5px;
            max-height: calc(100vh - 40px);
        }

        /*
        .title-bar {
            border-bottom: 1px solid black;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title-controls {
            padding: 10px;
        }

        .title {
            font-size: 1em;
            padding: 10px;
        }

        .window-content {
            padding: 10px;
            overflow-y: auto;
            max-height: calc(100vh - 150px);
        } */

        .about-box {
            display: flex;
            /* put 1em space between the two elements */
            gap: 1em;
            align-items: center;
            margin-bottom: 40px;
        }

        .my-image {
            width: 195px;
            max-width: 100%;
            height: 195px;
            object-fit: none;
            object-position: -150px -1px;
        }
    }

    .tech-icon {
        background: url('/images/tech.png');
        display: inline-block;
    }

    .python-icon {
        background-position: 2.101% 57.895%;
        width: 50px;
        height: 50px;
    }

    /* print styles */
    @media print {
        /* 1 inch border around the page */
        @page {
            margin: 1in;
        }
        body {
            font-size: 8pt;
        }
        .window {
            border: none;
            box-shadow: none;
            margin: 0 auto;
            max-width: 100%;
            max-height: 100%;
        }

        #foot {
            position: fixed;
            bottom: 0;
        }
        #lightdark-container {
            display: none;
        }
    }
    </style>
</head>

<body>
    <div class="window">
        <div class="title-bar">
            <div class="title">*Untitled - Notepad</div>
            <div class="title-controls">
                <span>🗙</span>
            </div>
        </div>
        <div class="window-content">
            <div class="about-box">

                <img class="my-image" src='/images/me.jpg' alt='PHP Logo'>
                <div id="about-text">
                    <h1>About</h1>
                    <p>
                        Hi, I am Ubaada. I am a Software Engineer based in New Zealand.
                    </p>
                    <p>
                        <a href="https://www.github.com/ubaada">GitHub</a>
                        <a href="https://www.twitter.com/ubaada">Twitter</a>
                        <a href="https://www.linkedin.com/in/ubaada-qureshi-995815228">LinkedIn</a>
                        <a href="https://huggingface.co/ubaada">HuggingFace</a>
                    </p>
                </div>
            </div>

            <div id="projects">
                <h1>Projects</h1>
                <p>
                    <a href="https://www.wordmap.ubaada.com/">Word Map:</a><br>
                    Maps how machine learning models associate word with different cultures and countries in vector
                    space.
                </p>

                <p>
                    <a href="http://vbe.ubaada.com/">VBE tool:</a><br>
                    A simple tool to convert Variable Byte Encoding to and from decimal numbers.
                </p>

                <p>
                    <a href="https://huggingface.co/datasets/ubaada/booksum-complete-cleaned">Cleaned BookSum
                        dataset:</a><br>
                    A cleaned version of the BookSum dataset published on HuggingFace. The dataset is a collection of book chapters, whole books,
                    and their summaries.
                    BookSum dataset is used for training and evaluating summarization machine learning models.
                </p>
                <p>
                    <a href="https://huggingface.co/collections/ubaada/my-booksum-models-6644bc3c3744e4bcd5b45078">
                        Summarization LLM Models:
                    </a><br>
                    Some Efficient Attention Transformer models fine-tuned on the BookSum dataset above for summarization. 
                    Efficient Attention, as opposed to the regular attention mechanism used in Transformer language models like ChatGPT,
                    allows us to process longer sequences of text more efficiently with less hardware resources.
                </p>
                <p>
                    <a href="https://github.com/ubaada/search-engine-wsj">
                        Search Engine
                    </a><br>
                    An information retriever (search engine) written in C for parsing and searching the 
                    WSJ collection using an inverted index.
                </p>
            </div>
            <div id="gh-stats-container">
                <h1>GitHub Stats</h1>
                <div id="gh-stats">
                    <?php
                        # loae the array from lang-stats.php
                        $languageData = include 'lang-stats.php';
                        # Sample data
                        #{"PHP":{"name":"PHP","color":"#4F5D95","size":103783,"count":4},
                        #"Java":{"name":"Java","color":"#b07219","size":86234,"count":2},
                        #"HTML":{"name":"HTML","color":"#e34c26","size":56001,"count":5}

                        # create a color bar for each language where the width is proportional to the size
                        $bar_container = "<div id='gs-bar-container'>";
                        $total = array_sum(array_column($languageData, 'size'));
                        foreach ($languageData as $lang) {
                            $bar_container = $bar_container . "<div class='gs-bar' style='background-color: 
                            {$lang['color']}; width: " . ($lang['size'] / $total * 100) . "%'></div>";
                        }
                        $bar_container = $bar_container . "</div>";

                        # create a list of labels for each language
                        $labels = "<div id='gs-all-labels'>";
                        foreach ($languageData as $lang) {
                            $percent = number_format(($lang['size'] / $total) * 100, 1);
                            $labels = $labels . "<div class='gs-label-box'><div class='gs-label-dot' 
                            style='background-color: {$lang['color']}'></div><div class='gs-label-text'>
                            {$lang['name']} - {$percent}%</div></div>";
                        }
                        $labels = $labels . "</div>";

                        echo $bar_container . $labels;
                    ?>
                </div>
            </div>
            <div id="posts">
                <h1>Recent Posts</h1>
                <p>
                    <?php
                        $max_posts = 5;
                        // Connect to the database
                        $pdo = new PDO('sqlite:../data.db');
                        $sql = 'SELECT * FROM posts WHERE status = "published" ORDER BY date DESC LIMIT ' . $max_posts;
                        $stmt = $pdo->prepare($sql);
                        // Execute the prepared statement and fetch all matching posts
                        $stmt->execute();
                        $all_posts = $stmt->fetchAll();
                        $tbl_html = "";
                        // latest article time/date (for last modified in the footer)
                        $latest_article_timestamp = $all_posts[0]['date'];
                        foreach ($all_posts as $post) {
                            $title = $post['title'];
                            $viewlink = '<a href="/post/' . $post['post_id'] . '">' . $post['title'] . '</a>';

                            # pDate = english-month - year.
                            $pDate = date('F Y', $post["date"]);
                            $tbl_html = $tbl_html . '<p><span class="post_date">' . $pDate . '</span>' . $viewlink . '</p>';
                        }
                        echo $tbl_html;
                    ?>
                    <br>
                <p><a href="/posts">View all posts</a></p>
                </p>
            </div>


            <div id="foot">
                <?php
                    $last_mod = filemtime(__FILE__);
                    $final_mod = max($last_mod, $latest_article_timestamp);

                    # Convert to NZT using DateTime and DateTimeZone
                    $dateTime = new DateTime("@$final_mod");
                    $dateTime->setTimezone(new DateTimeZone('Pacific/Auckland'));
                    $final_mod_nzt = $dateTime->format('d/F/Y');

                    echo "<span id='last-mod'>Last updated: $final_mod_nzt</span>";
                ?>
                <span>&#124;</span>
				<label id="lightdark-container">
					<input type="checkbox" id="lightdark-checkbox">
					<div id="lightdark-btn"></div>
				</label>
            </div>

            

        </div>
    </div>


</body>

</html>