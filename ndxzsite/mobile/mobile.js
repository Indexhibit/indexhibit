var indexhibit_mobile_index = (function()
{
    var state = 0, nav_toggle, close_layer, index;

    document.addEventListener('DOMContentLoaded', function()
    {
        nav_toggle = document.getElementById('nav-toggle');
        close_layer = document.getElementById('closing_layer');
        index = document.getElementById('index');

        // RESEARCH THE CLICK A LITTLE MORE ON THESE
        (nav_toggle.addEventListener) ? nav_toggle.addEventListener('click', indexhibit_mobile_index.displayer, false) : 
            nav_toggle.attachEvent('click', indexhibit_mobile_index.displayer);

        (close_layer.addEventListener) ? close_layer.addEventListener('click', indexhibit_mobile_index.displayer, false) : 
            close_layer.attachEvent('click', indexhibit_mobile_index.displayer);
    });

    var public = {

        displayer : function ()  
        {
            (state == 0) ? private.opener() : private.closer();
        }
    };

    var private = {

        opener : function ()  
        {
            nav_toggle.className = ' active';
            index.style.left = '0';
            private.fadeIn(close_layer);
            state = 1;
        }, 

        closer : function ()  
        {
            nav_toggle.className = '';
            index.style.left = '-100vw';
            close_layer.style.display = 'none';
            state = 0;
        },

        fadeIn : function (e)
        {
            // will this work in all mobile browsers
            e.style.opacity = 0;
            e.style.display = 'block';

            var last = +new Date();
            var tick = function() 
            {
                e.style.opacity = +e.style.opacity + (new Date() - last) / 200;
                last = +new Date();

                if (+e.style.opacity < 1) 
                {
                    (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16)
                }
            };
        }
    };

    return public;

})();