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
    @font-face {
        font-family: 'CascadiaCode';
        src: url('fonts/CascadiaCode.woff2') format('woff2');
    }

    html,
    body {
        /* mobile first, no margin or padding */
        margin: 0;
        padding: 0;
        font-size: 16px;
        font-family: 'CascadiaCode', monospace;
        font-weight: 300;
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


    #last-mod {
        margin: 70px 0 50px 0;
        color: grey;
        font-size: 0.8em;
    }

    /*when tablet or larger*/
    @media (min-width: 768px) {
        .window {
            /* border: 1px solid black; */
            margin: 20px auto;
            max-width: 800px;
            border-radius: 5px;
            max-height: calc(100vh - 40px);
        }




        /* For notepad effect - disabled

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
        }

        .my-image {
            width: 200px;
            max-width: 100%;
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
                    </p>
                    <p>
                        <!--
                
                    Technologies I like and use:
                    <span class="tech-icon python-icon"></span>
                    -->
                    </p>
                </div>
            </div>



            <div id="projects">
                <h1>Projects</h1>
                <p>
                    <a href="https://www.wordvoyage.ubaada.com/">Word Map:</a><br>
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
                    A cleaned version of the BookSum dataset. The dataset is a collection of book chapters, whole books,
                    and their summaries.
                    BookSum dataset is used for training and evaluating summarization machine learning models.
                </p>
            </div>

            <div id="posts-container">
                <h1>Posts</h1>
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
                        $latest_article_date = date('d/F/Y', $all_posts[0]['date']);
                        foreach ($all_posts as $post) {
                            $title = $post['title'];
                            $viewlink = '<a href="/post/' . $post['post_id'] . '">' . $post['title'] . '</a>';

                            # pDate = english-month - year.
                            $pDate = date('F Y', $post["date"]);
                            $tbl_html = $tbl_html . '<p>' . $viewlink . '<br>(' . $pDate . ')</p>';
                        }
                        echo $tbl_html;
                    ?>
                    <br>
                <p><a href="/posts">View all posts</a></p>
                </p>
            </div>


            <div id="last-mod">
                <?php
                $last_mod = filemtime(__FILE__);
                $last_mod_static = date('d/F/Y', $last_mod);
                $final_mod = max($last_mod, $latest_article_date);
                $final_mod = date('d/F/Y', $final_mod);

                echo "<p>Last modified: $final_mod</p>";

            ?>
            </div>

        </div>
    </div>


</body>

</html>