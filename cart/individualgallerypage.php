<!-- 
This shows the code associated with each gallery page. An example can be seen here: https://www.jondstone.com/galleries/greenwithenvy.php The below JavaScript is stored in a separate file and then referenced, allowing all pages to be changed at once. In addition, there is a reference to jquery.min.js, without it, this code will not work correctly.

The HTML for the drop-down selections is stored in a single file, and is read and displayed onto the page using PHP. This allows a single change to affect all pages.

How the drop-down selections work:
The user must select a style, then based on their selection, it will show finishes for that style, once they select a finish, then they are presented a new drop-down showing which sizes are available. If the user goes back and selects a new style, then size is removed, while finish is repopulated. If they select a new finish, then the size drop-down is repopulated.

Although I currently have limited styles available, this will change in the future, where those styles 
have unique finishes. Therefore the below code has already been built to handle this. 

All code written by hand; searched for guidance on Stack Overflow when stuck.
-->
<html>
    <script>
    //calls the cart on load
    $(document).ready(function () {
        updateCart();
    });

    //handles new drop-down upon selection
    $(document).ready(function(){
        var $style = $('select[name=style]'),
            $finish = $('select[name=finish]'),
            $size = $('select[name=size]');

        $style.change(function(){
            var $this = $(this).find(':selected'),
                rel = $this.attr('rel'),
                $set = $finish.find('option.' + rel);

            if ($set.size() < 0) {
                $finish.hide();
                return;
            }

            $finish.show().find('option').hide();

            $set.show().first().prop('selected', true);

        });


      $finish.change(function(){
            var $this = $(this).find(':selected'),
                rel = $this.attr('rel'),
                $set = $size.find('option.' + rel);

            if ($set.size() < 0) {
                $size.hide();
                return;
            }

            $size.show().find('option').hide();

            $set.show().first().prop('selected', true);

        });
    });    

    //handles parsing data from drop-down selections and passing to addToCart
    var print_Style,
        abbrv_Finish,
        print_Size,
        print_Price,
        _toCart,
        finish_Counter = 0,
        size_Counter = 0;

        //stores Print Style selection
        function showStyle(element) {
            print_Style = element.options[element.selectedIndex].text;

            if (finish_Counter > 0){
                document.getElementById('show_Size').innerHTML = '';
                document.getElementById('print_Cost').innerHTML = '';
                document.getElementById('cart_Button').innerHTML = '';
                document.getElementById("show_SizeSelections").style.display = 'none';
            }
            else{
                document.getElementById("show_Finish").innerHTML = '<h4>2. Select a Finish</h4>';
            }
            finish_Counter++;
        }
        //stores Print Finish selection
        function showFinish(element) {
            var finish_Style = element.options[element.selectedIndex].text;

            if (finish_Style === 'Luster'){
                abbrv_Finish = 'L';
            }
            else if (finish_Style === 'Metallic'){
                abbrv_Finish = 'M';
            }

            if (size_Counter > 0){
                document.getElementById('print_Cost').innerHTML = '';
                document.getElementById('cart_Button').innerHTML = '';
                document.getElementById("show_Size").innerHTML = '<h4>3. Select a Size</h4>';
                document.getElementById("show_SizeSelections").style.display = 'block';
            }
            else{
                 document.getElementById("show_Size").innerHTML = '<h4>3. Select a Size</h4>';
            }
            size_Counter++;
        }

        //stores Print Size and Price selection
        function showSize(element) {
            var szpr_Selection = element.options[element.selectedIndex].text;
            var szpr_Split = szpr_Selection.split(" ");
            print_Size = szpr_Split[0];
            print_Price = szpr_Split[1].replace('$','');

            //if Price exists, display it and Add2Cart
            if (print_Price != undefined) {
                document.getElementById("print_Cost").innerHTML = '<h4>' + '&nbsp;Total: $' + print_Price + '</h4>';

                //add items for onClick event
                _toCart = print_Name + '_' + print_Style + '_' + abbrv_Finish + '_' + print_Size + '_' + print_Price;
                document.getElementById('cart_Button').innerHTML = "<div class='show-image thumbnail'><a style='text-decoration:none;' onclick='addToCart(\"" + _toCart + "\")'>Add To Cart</a></div>";
            }
        }

    //adds item and calls updateCart      
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip({ 'placement': 'bottom' });
        updateCart();
    });

    function addToCart(itemName) {
        localStorage.setItem(generateUUID(), itemName);
        $.toaster({ priority : 'success', message : 'Item was added to cart'});
        fbq('track','AddToCart');
        updateCart();
    }

    function updateCart() {
        var cartData = "";
        var counter = 0;
        cartData += "";

        if(localStorage.length > 0)
            $('#cartIconText').text(localStorage.length);
        else
            $('#cartIconText').text('');
    }

    //generates uuid
    function generateUUID() {
        var d = new Date().getTime();
        if (window.performance && typeof window.performance.now === "function") {
            d += performance.now(); //use high-precision timer if available
        }
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (d + Math.random() * 16) % 16 | 0;
            d = Math.floor(d / 16);
            return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
        return uuid;
    }

    //tied into updating the shopping cart count
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');

        fbq('init', '1190114291020684');
        fbq('track', "PageView");

    //updates Facebook like count
    (function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

    //display "add to cart" when button clicked, and incrememnts count
    (function ($,undefined)
    {
        var toasting =
        {
            gettoaster : function ()
            {
                var toaster = $('#' + settings.toaster.id);

                if(toaster.length < 1)
                {
                    toaster = $(settings.toaster.template).attr('id', settings.toaster.id).css(settings.toaster.css).addClass(settings.toaster['class']);

                    if ((settings.stylesheet) && (!$("link[href=" + settings.stylesheet + "]").length))
                    {
                        $('head').appendTo('<link rel="stylesheet" href="' + settings.stylesheet + '">');
                    }

                    $(settings.toaster.container).append(toaster);
                }

                return toaster;
            },

            notify : function (title, message, priority)
            {
                var $toaster = this.gettoaster();
                var $toast  = $(settings.toast.template.replace('%priority%', priority)).hide().css(settings.toast.css).addClass(settings.toast['class']);

                $('.title', $toast).css(settings.toast.csst).html(title);
                $('.message', $toast).css(settings.toast.cssm).html(message);

                if ((settings.debug) && (window.console))
                {
                    console.log(toast);
                }

                $toaster.append(settings.toast.display($toast));

                if (settings.donotdismiss.indexOf(priority) === -1)
                {
                    var timeout = (typeof settings.timeout === 'number') ? settings.timeout : ((typeof settings.timeout === 'object') && (priority in settings.timeout)) ? settings.timeout[priority] : 1500;
                    setTimeout(function()
                    {
                        settings.toast.remove($toast, function()
                        {
                            $toast.remove();
                        });
                    }, timeout);
                }
            }
        };

        var defaults =
        {
            'toaster'         :
            {
                'id'        : 'toaster',
                'container' : 'body',
                'template'  : '<div></div>',
                'class'     : 'toaster',
                'css'       :
                {
                    'position' : 'fixed',
                    'top'      : '20px',
                    'right'    : '20px',
                    'width'    : '300px',
                    'zIndex'   : 50000
                }
            },

            'toast'       :
            {
                'template' :
                '<div class="alert alert-%priority% alert-dismissible" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '<span class="sr-only">Close</span>' +
                    '</button>' +
                    '<span class="title"></span>: <span class="message"></span>' +
                '</div>',

                'defaults' :
                {
                    'title'    : 'Notice',
                    'priority' : 'success'
                },

                'css'      : {},
                'cssm'     : {},
                'csst'     : { 'fontWeight' : 'bold' },

                'fade'     : 'slow',

                'display'    : function ($toast)
                {
                    return $toast.fadeIn(settings.toast.fade);
                },

                'remove'     : function ($toast, callback)
                {
                    return $toast.animate(
                        {
                            opacity : '0',
                            padding : '0px',
                            margin  : '0px',
                            height  : '0px'
                        },
                        {
                            duration : settings.toast.fade,
                            complete : callback
                        }
                    );
                }
            },

            'debug'        : false,
            'timeout'      : 3500,
            'stylesheet'   : null,
            'donotdismiss' : []
        };

        var settings = {};
        $.extend(settings, defaults);

        $.toaster = function (options)
        {
            if (typeof options === 'object')
            {
                if ('settings' in options)
                {
                    settings = $.extend(true, settings, options.settings);
                }
            }
            else
            {
                var values = Array.prototype.slice.call(arguments, 0);
                var labels = ['message', 'title', 'priority'];
                options = {};

                for (var i = 0, l = values.length; i < l; i += 1)
                {
                    options[labels[i]] = values[i];
                }
            }

            var title    = (('title' in options) && (typeof options.title === 'string')) ? options.title : settings.toast.defaults.title;
            var message  = ('message' in options) ? options.message : null;
            var priority = (('priority' in options) && (typeof options.priority === 'string')) ? options.priority : settings.toast.defaults.priority;

            if (message !== null)
            {
                toasting.notify(title, message, priority);
            }
        };

        $.toaster.reset = function ()
        {
            settings = {};
            $.extend(settings, defaults);
        };
    })(jQuery);
    </script>
    
    <!-- 
    Below is only the necessary code used for the drop-downs and necessary features to make the cart work
    All other HTML has been left out. 
    I have only populated a few selections, to show it works. You can create as many or as little as you want.
    -->
    <div class="purchase_Options"><a href="/cart/index.php" target="_blank" id="cartID" title="cart"><i class="fa fa-shopping-cart fa-2x">&nbsp;<span class="menuitem" id="cartIconText"></span></i></a></div>
    <div class="selected_Dropdowns">
        <select name="style" onChange="showStyle(this);">
            <option value="0">- Select a Style -</option>
            <option value="0" rel="print">Print</option>
            <option value="0" rel="matted_print">Matted Print</option>
            <option value="0" rel="canvas">Canvas</option>
        </select>
        <br />
        <div id="show_Finish"></div>
        <select name="finish" class="finish" onchange="showFinish(this);">
            <option value="1" class="print">- Select a Finish -</option>
            <option value="1" rel="p_l_size" class="print">Luster</option>
            <option value="1" rel="p_m_size" class="print">Metallic</option>

            <option value="1" class="matted_print">- Select a Finish -</option>
            <option value="1" rel="mp_l_size" class="matted_print">Luster</option>
            <option value="1" rel="mp_m_size" class="matted_print">Metallic</option>

            <option value="1" class="canvas">- Select a Finish -</option>
            <option value="1" rel="cvs_l_size" class="canvas">Luster</option>
            <option value="1" rel="cvs_m_size" class="canvas">Metallic</option>
        </select>
        <br />
        <div id="show_Size"></div>
        <div id="show_SizeSelections">
            <select name="size" class="size" onchange="showSize(this);">
                <option value="2" class="p_l_size">- Select a Size -</option>
                <option value="2" class="p_l_size">8x12 $5</option>
                <option value="2" class="p_l_size">12x18 $10</option>
                <option value="2" class="p_m_size">- Select a Size -</option>
                <option value="2" class="p_m_size">8x12 $10</option>
                <option value="2" class="p_m_size">12x18 $20</option>

                <option value="2" class="mp_l_size">- Select a Size -</option>
                <option value="2" class="mp_l_size">8x12 $5</option>
                <option value="2" class="mp_l_size">12x18 $10</option>
                <option value="2" class="mp_m_size">- Select a Size -</option>
                <option value="2" class="mp_m_size">8x12 $10</option>
                <option value="2" class="mp_m_size">12x18 $20</option>

                <option value="2" class="cvs_l_size">- Select a Size -</option>
                <option value="2" class="cvs_l_size">8x12 $5</option>
                <option value="2" class="cvs_l_size">12x18 $10</option>
                <option value="2" class="cvs_m_size">- Select a Size -</option>
                <option value="2" class="cvs_m_size">8x12 $10</option>
                <option value="2" class="cvs_m_size">12x18 $20</option>
            </select>
        </div>
    </div>
    <br /><br />
    <div id="print_Cost"></div>
    <div id="cart_Button"></div> 
    
</html>