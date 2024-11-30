<!DOCTYPE html>
<html lang="en-US">
<head>
	<!-- 
	Wrote JS code to expand/collapse text, to keep this page less busy.
    Original code relied on using a large and burdensome JQUERY file.
    Refactored this code in Nov. 2024 to break the dependency on the above
    and use less code throughout every page.
	-->
    <!-- Header content removed as it's not necessary to be displayed in Git. -->
</head>

<body>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Gather all elements with the class 'minimize'
            const minimizedElements = document.querySelectorAll('.minimize');
            const maxLines = 0; // max number of lines to display before truncating

            minimizedElements.forEach(function (element) {
                // Split the content of the element by <br> tags
                const textArr = element.innerHTML.replace(/\n?<br>/gi, "<br>").split(/<br>/);
                const countLines = textArr.length;

                // If the number of lines exceeds maxLines, split the content
                if (countLines > maxLines) {
                    const textLess = textArr.slice(0, maxLines).join("<br>");
                    const textMore = textArr.slice(maxLines).join("<br>");

                    // Modify the HTML to show truncated content with 'Show More' and 'Show Less' links
                    element.innerHTML = `
                        ${textLess}
                        <div></div>
                        <a href="#" class="more">&or;&nbsp;Show More</a>
                        <div style="display:none;">
                            <a href="#" class="less">&and;&nbsp;Show Less</a><br />
                            ${textMore}
                        </div>
                    `;
                }
            });

            // Add event listener for 'Show More' links
            document.addEventListener("click", function (event) {
                if (event.target.classList.contains("more")) {
                    event.preventDefault(); // Prevent the default link behavior
                    const moreLink = event.target;
                    moreLink.style.display = "none"; // Hide the 'Show More' link
                    moreLink.nextElementSibling.style.display = "block"; // Show the full text
                }
            });

            // Add event listener for 'Show Less' links
            document.addEventListener("click", function (event) {
                if (event.target.classList.contains("less")) {
                    event.preventDefault(); // Prevent the default link behavior
                    const lessLink = event.target;
                    const fullContentDiv = lessLink.parentElement;
                    fullContentDiv.style.display = "none"; // Hide the full text
                    fullContentDiv.previousElementSibling.style.display = "inline"; // Show the 'Show More' link
                }
            });
        });
    </script>
	<noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>

	<div id="content">
		<br />
		<div class="anchor_links">
			<h1><a href="#stylesandfinishes">Styles &amp; Finishes</a></h1>
		</div>
        
        <h2><a name="stylesandfinishes">Styles &amp; Finishes</a></h2>
		<br />
        
        <div id="stylesfinishes">
            <div class="indvd_Finish">
                <p class="info">Framed Plaques</p><br />
                <p class="tagline">Unique. Alluring. 3D-effect.</p>
                <br /><br />
                <div class="minimize">
                    This style has been designed to move away glass and in doing so reduces the weight, cost, and glare. A pressure-mounted, UV-, and scratch-resistant coating is used to protect the
                    piece from water and UV damage.
                    <br /><br />
                    The photo is placed on a masonite backing with beveled edges, then the piece is mounted onto a black mat that is recessed in a 'shadow box' black frame. The black mat helps
                    draw out detail in the image, while the photo being raised forwards draws your attention to it before anything else.
                    <br /><br />
                    Prints are archival up to 100 years and with the UV laminate the lifetime is extended. All materials are acid free. Framed Plaques come ready to be hung.
                    <br /><br />
                    <div class="table_format">
                        <table cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Print Size</td>
                            <td>12&#215;18</td>
                            <td>16&#215;24</td>
                            <td>20&#215;30</td>
                            <td>24&#215;36</td>
                        </tr>
                        <tr>
                            <td>Mat Size</td>
                            <td>16&#215;22</td>
                            <td>20&#215;28</td>
                            <td>24&#215;34</td>
                            <td>28&#215;40</td>
                        </tr>
                        <tr>
                            <td>Frame Size</td>
                            <td>17&#215;23</td>
                            <td>21&#215;29</td>
                            <td>25&#215;35</td>
                            <td>29&#215;41</td>
                        </tr>
                        </table>
                    </div>
                    <br />
                </div>
                <img src="images/framedplaque.jpg" alt="Framed Plaques"/>
            </div>
            <div class="indvd_Finish">
                <p class="info">Canvas</p><br />
                <p class="tagline">Classical. Professional. Sturdy.</p>
                <br /><br />
                <div class="minimize">
                    A gallery wrapped canvas can provide a budget conscious alternative to framed pictures. Canvases can typically provide a softer image that
                    in general creates a 'painted' feel, while still maintaining its vibrant color.
                    <br /><br />
                    Each canvas is pre-coated with a semi-matte before printing. The semi-matte helps to enrich the blacks and make for a nice smoother look. To ensure the canvas against temperature and humidity changes, each piece is stretched well and then stapled to its frame. Most canvases are glued to its backing, which over time will produce an uneveness on the surface.
                    <br /><br />
                    Sides are 1.5" in depth unless requesed to be different, and all canvases come with a wire making them ready for hanging.
                    <br /><br />
                </div>
                <img src="images/canvas.png" alt="Canvas"/>
            </div>
            <div class="indvd_Finish">
                <p class="info">Prints &amp; Mats</p><br />
                <p class="tagline">Customizable. Affordable. Matchable.</p>
                <br /><br />
                <div class="minimize">
                    If you would like a more affordable alternative to the much sought after Framed Plaques and Canvases, then look no further. These pieces can be bought either with or without the mat and backing.
                    <br /><br />
                    Each print is printed on Luster or Metallic paper at a local professional photo lab; after which each print is carefully inspected by me for any imperfections resulting
                    from the printing process. Traditionally these photos are mounted on a foamcore backing and a cream mat (white core) is placed over the photo. If you would like a different style or color of mat, please contact me before purchasing.
                    <br /><br />
                    Note: the actual image size of a 12x18 print matted will be closer to 11x17. Please see the different size options below. Custom sizes and options are available but please be aware there is an upper limit due to the size of the mat boards I receive.
                    <br /><br />
                    All prints are archival up to 100 years and all mats and backing are acid free.
                    <br /><br />
                    <div class="table_format">
                        <table cellspacing="0" cellpadding="0">
                        <tr>
                            <td>Print Size</td>
                            <td>8&#215;12</td>
                            <td>12&#215;18</td>
                            <td>16&#215;24</td>
                            <td>20&#215;30</td>
                            <td>24&#215;36</td>
                        </tr>
                        <tr>
                            <td>Mat Size</td>
                            <td>12&#215;16</td>
                            <td>16&#215;22</td>
                            <td>22&#215;30</td>
                            <td>24&#215;36</td>
                            <td>32&#215;40</td>
                        </tr>
                        </table>
                    </div>
                    <br />
                </div>
                <img src="images/prints.jpg" alt="Matted Prints"/>
            </div>
            <div id="clear"></div>
		</div>
		
		<!-- Rest of the Page's content removed as it's not necessary to be displayed in Git. -->

</body>
</html>