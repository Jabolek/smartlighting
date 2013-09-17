------------ PARAMS ------------
Script honors GET, POST and CLI variables
--------------------------------
1 - road_coords - list of road x,y coordinates separated by semicolon REQUIRED
2 - road_radius - radius of road to cover with lanterns REQUIRED
3 - bulb_radius - radius of bulb used to cover the road REQUIRED
4 - enable_scaling - given the road_radius and bulb_radius are given in meters 
    and road_coords are lat/long values, transforms those values to degrees.
    VALUES: 1: convert DEFAULT
            0: do not convert
5 - optput_type type of data returned
    VALUES: 1: JSON
            2: TSV
--------------------------------
EXAMPLE WWW USAGE: 
[PATH_TO_FILE]/lp.php?road_coords=0,0;1,5;2,10;3,11;3,14;3,12;16,40;16,60&road_radius=0.5&bulb_radius=2&enable_scaling=0
--------------------------------
EXAMPLE COMMAND LINE:
lp.php 0,0;1,5;2,10;3,11;3,14;3,12;16,40;16,60 0.5 2 0