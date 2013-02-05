<!DOCTYPE html>
<html lang="ko">
<head>
<title>Create a Custom Select Box with jQuery - Onextrapixel</title>

<style>
/*So how does this all work?

Firstly I put the search elements into a containing div.   This div includes our grey background colour etc.*/

.search-container {
    width: 925px;
    height: 63px;
    background-color: #e1e1e1;
    position: relative;
}

/* Next I formatted the select box based on the design provided.  This involved setting the width, height etc.  Make sure you set the background colour to transparent so that our custom arrow will show later down the track.
We also need to include -webkit-appearance : none; to get it to work properly in Chrome. */

.search-box select {
    width: 137px;
    height: 50px;
    border: 0;
    background-color: transparent;
    color: #4d4d4d;
    font-family: 'oswaldregular', Calibri;
    text-transform: uppercase;    
    font-size: 22px;
    padding: 10px 0 5px 6px;
    cursor:pointer;
    -webkit-appearance: none;
}

/* The select boxes need to be positioned in line with each other, so to do this I added some CSS to our search-box class. */

.search-box {
    display: inline-block;
    height: 50px;
    padding-top: 7px;
    padding-left: 7px;
    position:relative;
    width:128px;
}

/* Because I cannot format the drop down directly using CSS, I want to hide it instead.  To do this I put the select box in another containing div and set the width to less than what the width of the actual select bow is set to.  I then set the overflow on my containing div to hidden.  This will hide the drop down arrow. 
Note: Each select box will have its own unique container as they are both different sizes. */

.size-container {
    overflow: hidden;
    width: 116px;
    display: inline-block;
}
.bedrooms-container {
    overflow: hidden;
    width: 86px;
    display: inline-block;
}

/* Since we have hidden the drop down arrow we need to replace it with our own arrow.  To do this we need an image of our new arrow.  Then add the arrow as a background image to our overflow div, setting position as right and also applying the background colour of what we want the select box to look like. */

.size-container {
    overflow: hidden;
    width: 116px;
    display: inline-block;
    background: url("../images/new_arrow.png") no-repeat right #c8c8c8;
}
.bedrooms-container {
    overflow: hidden;
    width: 86px;
    display: inline-block;
    background: url("../images/new_arrow.png") no-repeat right #c8c8c8;
}

/* Finally we need to apply some formatting to our labels.  To do this I applied the following CSS. */

.search-container span {
    line-height: 69px;
    padding-left: 12px;
    vertical-align: top;    
    font-family: 'oswaldregular',Calibri;
    text-transform: uppercase;    
    color: #000000;
    font-size: 22px;
}
</style>
</head>

<body>

<div class="search-container">
    <span>Size</span>
    <div class="search-box">
        <div class="size-container">
        <select name="size" id="size"> 
            <option value="0">ANY</option>
            <option value="100">100-200</option>
            <option value="200">200-300</option>
            <option value="300">300-400</option>
            <option value="400">400-500</option>
            <option value="500">500-600</option>
            <option value="600">600+</option>
        </select>
        </div>
    </div><span style="padding-left:20px;">Bedrooms</span>
    <div class="search-box" style="left:6px; width:98px;">
        <div class="bedrooms-container">
        <select name="bedrooms" style="width:103px;"> 
            <option value="0">ANY</option>
            <option value="1">ONE</option>
            <option value="2">TWO</option>
            <option value="3">THREE</option>
            <option value="4">FOUR</option>
            <option value="5">FIVE</option>
            <option value="6">SIX</option>
            <option value="7">SEVEN+</option>
        </select>
        </div>
    </div>
</div>


</body>
</html>