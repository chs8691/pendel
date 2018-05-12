# pendel
Wordpress plugin: Image Wallpaper for GPS tracked images

With this plugin, a canvas with image tiles can be shown on a wordpress page. The tiles will be placed by geo coordinates, stored in the separate file gps.csv. In this file, title and description of every image must stored too. The tiles can be clicked to show the image in an viewer. The canvas has a vertical scroll bar and the browsers scrollbar is hidden. With the scrollbar, the user can scroll in the past, by hiding the last tile. 
In the first place, the plugin is the implementation of my Pendel blog and less for common usage. You can't find it in the Wordpress' plugin directory and it can't be used easily and out-of-the-box. To provide a fullscreen canvas, the resonar theme is inherited, so resonar must be installed too. The child theme is part of this repository. The images must be stored in a subdirectory of the upload folder, so for instance ftp access is needed to add content.

![Canvas](/documentation/canvas.png)

To see the plugin in action, visit the page 'Leinwand' at my Pendel Blog on http://earls5.menkent.uberspace.de/pendel

## Installation
Precondition for using this plugin is an existing Wordpress installation and ftp access to the web content. The **theme** resonar must be used. If you want to use another theme, than you must write your own child theme.
First the themes should be installed. In wordpress, install theme Resonar from the theme repository. To install the child theme, you must be logged in by ftp (or someting else). Then, copy the folder resonar-pendel-child in the themes folder. After that, the themes folder should look like this:
* themes
  * resonar
  * resonar-pendel-child
    * template-full-width.php
    * ...
  
The **plugin** can be installed in the same way. Just copy the directory pendel into plugin:
* plugins
  * pendel
    * index.php
    * pendel-configuration.php
    * ...
    
The last step is to create the upload subdirectories. First create the folder 'pendel'. This is the root folder for all content of all canvas' (there can be multiples canvas' in one blog). Give your first canvas a name and create a eponymous subfolder, for instance 'ffm'. 
In this folder all content files will be stored. The upload directory can now look like this:
* uploads
  * 2017
  * 2018
  * pendel
    * ffm
 
That's all, now the canvas page can be defined.

## Administration
Log as admin into Wordpress download the theme 'Resonar'. Then activate the resonar-child-theme. After that, activate the plugin 'Pendel Image Wallpaper'.

## Create page
The canvas can be used on (empty) pages. If you want to use a canvas on a blog post, you maybe must edit the theme. I did not test this. Create an new page and set the theme to 'Resonar Pendel Full Width'. Now, define the canvas by using this sample configuration as the page content:

    [pendel: id="ffm", x="4000", y="2000", tile="80", start_lon="8.615272", start_lat="50.2031599", end_lon="9.1137766", end_lat="50.0544646", refreshcode="123"]

The configuration starts with an opened square bracket, directly followed by the string 'pendel: '. This is followed by a several key value pairs, separated by a colon and a blank space. Every value is enclosed in quotation marks. Let's explain the key value-pairs now in detail by the example data:

parameters    | Description
------------ | -------------
id="ffm" | ID for this canvas and name of the uploads subfolder. Must be blog-unique. 
x="4000" | Size in pixel of the canvas X-coordinate
y="2000" | Size in pixel of the canvas Y-coordinate
tile="80" | Size of the squared tile in pixel
start_lon="8.615272" | Upper left corner of ...
start_lat="50.2031599" | ...the canvas 
end_lon="9.1137766" | Lower right corner of ...
end_lat="50.0544646" | ...the canvas
refreshcode="123" | Query parameter of the URI for refreshing the database after adding new images

Save this page and call it in a browser. If the plugin works, an empty canvas should be shown.

## Adding images
Now it's time to add the first image to the canvas. For every image that must be placed on the canvas, three things are needed:
1. Image file in landscape with size 1800 x 1200 pixel, to be shown in the image viewer
1. Small image/section file with size 100 x 100 pixel
1. Item in the gps.csv file with file name, title, description and geo coordinates. 

Example for a line in file gps.csv:

    20170329-165427-DSCF4795.jpg<TAB>50.103855 8.70820270007778<TAB>Pendel II<TAB>Under Cover

Hints:
* The viewer is optimized for the size 1800 x 1200. If you want to use a different aspect ratio, the viewer coding must be edited.
* The small square image is show on the canvas. The name of this file must be the same as the image file, but with the prefix 'tile_'. If you want to show non-square tiles, the canvas coding must be edited.
* The EXIF data of the images will not be used.

The item for an image in the file gps.csv has following values, separated by tabs
* Image file name
* Latitude Longitude (separated by a blank space)
* Title
* Description (sub title)

Copy the image file and the tile file into the content directory and update/create the gps.csv file. The uploads directory now should look like this:
* uploads
  * pendel
    * ffm
      * 20170329-165427-DSCF4795.jpg
      * tile_20170329-165427-DSCF4795.jpg
      * gps.csv
      
To update the database for the changed content, you have either to deactivate/activate the plugin or to call the page with the refreshcode parameter, that was defined in the page, e. g. 

    localhost:8080/wordpress/sample-page/?refreshcode=123
