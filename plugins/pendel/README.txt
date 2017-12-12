Instructions for Pendel - Wordpress - Plugin
============================================

- Attribute changes need an plugin deactiavtion
If settings in the page configuration are changed [pendel...], the plugin must 
be restarted.

- Pendel configuration
To show a pendel in a page, there must be a one line configuration like this:

    [pendel: id="ffm", x="4000", y="2000", tile="80", start_lon="8.615272", 
    start_lat="50.2031599", end_lon="9.1137766", end_lat="50.0544646"]

Parameter descriptions:
    id="ffm" Unique ID for the pendel, String(1..3)
    x="4000" Canvas size in pixel
    y="2000" Canvas size in pixel
    tile="80" Tile size in pixel
    start_lon="8.615272" Canvas' top left coordinate longitude as decimal
    start_lat="50.2031599" Canvas' top left coordinate Latitude as decimal
    end_lon="9.1137766" Canvas' bottom right coordinate longitude as decimal
    end_lat="50.0544646"  Canvas' bottom right coordinate Latitude as decimal 



- Only one usage supported
There can only be one page per wordpress installation used.

- Structured upload directory
Images and the gps file must be located in a subfolder 'pendel/<id> of the uploads directory.

In Wordpress | Dashboard | Settings | Media disable 
'Organize my uploads into month- and year-based folders'

- sftp File access 
Take care for right permissions of the files, if you use (s)ftp.
If a tile image isn't show but only the shadow, 
the cause could be missing file permissions.

- Image and gps Files
There must an image file and a tile file with a size of 100 x 100 px in the upload directory.
All image files and their gps coordinates must be listed in an file gps.csv, located in the upload directory.
Line format (Take care for tabs and the space between lat/lon
<image name><TAB><Latitude> <Longitude><TAB>Title<TAB><Description>
Example of gps.csv:
20170313-180536-DSCF4779.jpg	50.1555543999528 8.95522409999722	A title	A description
20170329-165427-DSCF4795.jpg	50.103855 8.70820270007778	A title	A description
20170403-163956-DSCF4838.jpg	50.0991754000444 8.749723299925	A title	A description
20170529-155643-DSCF5012.jpg	50.0700422999972 8.713579900125	A title	A description
20170612-162213-DSCF5091.jpg	50.0761149000667 8.7744936	A title	A description
20170821-180935-DSCF5738.jpg	50.078565 8.7814432	A title	A description
20170823-074258-DSCF5765.jpg	50.1238147 8.95134789999139	A title	A description
20170823-190220-DSCF5771.jpg	50.1330646000083 8.99673729998889	A title	A description



