#CSS post processing

To improve our CSS, we post-process it with [pleeease](http://pleeease.io/).
It :
* adds prefixes, based on Autoprefixer
* provides fallbacks for rem unit, CSS3 pseudo-elements notation
* adds opacity filter for IE8
* converts CSS shorthand filters to SVG equivalent
* packs same media-query in one @media rule
* inlines @import styles
* minifies the result

Kind of magic, isn't it ?

To use it, you need to install node-js, and then  : ```npm install -g pleeease```
To process CSS, you just have to say (in command line) : ```pleeease compile ``` in the CSS folder
