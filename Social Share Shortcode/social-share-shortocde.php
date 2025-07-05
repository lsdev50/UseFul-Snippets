<?php

function collapsible_social_share_shortcode() {
    if ( ! ( is_singular() && is_product() ) ) return '';

    $url   = urlencode( get_permalink() );
    $title = urlencode( get_the_title() );

    ob_start();
    ?>

    <style>
        .collapsible-share-wrapper { position: relative; font-family: "lato"; }
        .share-toggle-button { background-color: transparent; color: #ef7d00; border: 1px solid #ef7d00; padding: 10px 18px; font-family: "lato"; height: 45px; font-size: 16px; font-weight: 600; cursor: pointer; border-radius: 5px; display: flex; align-items: center; }
        .share-toggle-button i { margin-right: 8px; }
        .share-icons { position: absolute; top: 100%; left: 0; z-index: 999; display: none; flex-wrap: wrap; gap: 10px; margin-top: 12px; background: #fff; padding: 12px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); animation: slideDown 0.3s ease forwards; max-width: 200px }
        @keyframes slideDown { 0% { opacity: 0; transform: translateY(-10px); } 100% { opacity: 1; transform: translateY(0); } }
        .share-icons a, .share-copy-button { display: inline-flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; color: #fff; font-size: 16px; text-decoration: none; transition: transform 0.2s ease; cursor: pointer; }
        .share-icons a:hover, .share-copy-button:hover { transform: scale(1.1); }
        .facebook { background-color: #3b5998; }
        .twitter { background-color: #1da1f2; }
        .linkedin { background-color: #0077b5; }
        .whatsapp { background-color: #25d366; }
        .reddit { background-color: #ff4500; }
        .telegram { background-color: #0088cc; }
        .email { background-color: #6c757d; }
        .copy-btn { background-color: #6f42c1; }
        .share-feedback { margin-top: 10px; font-size: 14px; color: green; display: none; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <div class="collapsible-share-wrapper">
        <button class="share-toggle-button" onclick="toggleShareMenu(this)"> <i class="fas fa-share-alt"></i> Share </button>
        <div class="share-icons">
            <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a class="twitter" href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" title="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
            <a class="linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>&title=<?php echo $title; ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a class="whatsapp" href="https://api.whatsapp.com/send?text=<?php echo $title . '%20' . $url; ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <a class="reddit" href="https://www.reddit.com/submit?url=<?php echo $url; ?>&title=<?php echo $title; ?>" target="_blank" title="Reddit"><i class="fab fa-reddit-alien"></i></a>
            <a class="telegram" href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
            <a class="email" href="mailto:?subject=<?php echo $title; ?>&body=<?php echo $url; ?>" title="Email"><i class="fas fa-envelope"></i></a>
            <div class="share-copy-button copy-btn" onclick="copyShareURL('<?php echo get_permalink(); ?>')"> <i class="fas fa-link"></i> </div>
        </div>
        <div class="share-feedback">✅ Link copied to clipboard!</div>
    </div>

    <script>
        function toggleShareMenu(button) {
            const menu = button.nextElementSibling;
            const feedback = menu.nextElementSibling;
            menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
            feedback.style.display = 'none';
        }

        function copyShareURL(url) {
            navigator.clipboard.writeText(url).then(() => {
                const feedback = document.querySelector('.share-feedback');
                feedback.style.display = 'block';
                setTimeout(() => feedback.style.display = 'none', 2500);
            });
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.querySelector('.collapsible-share-wrapper');
            if (!wrapper.contains(e.target)) {
                wrapper.querySelector('.share-icons').style.display = 'none';
                wrapper.querySelector('.share-feedback').style.display = 'none';
            }
        });

        window.addEventListener('scroll', function() {
            document.querySelectorAll('.share-icons').forEach(menu => menu.style.display = 'none');
            document.querySelectorAll('.share-feedback').forEach(msg => msg.style.display = 'none');
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'social_share', 'collapsible_social_share_shortcode' );