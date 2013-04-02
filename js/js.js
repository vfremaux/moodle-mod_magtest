function updaterenderer(n, selector){
    imgpath = '<?php echo $renderingpathbase ?>'+selector.options[selector.selectedIndex].value;
    imgid = 'symbol_img' + n;
    imgobj = document.getElementById(imgid);
    imgobj.src = imgpath;
}
