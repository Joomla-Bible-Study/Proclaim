#!/bin/bash
2  #-----------------------------------------------------------
3	#
4	# Purpose: Run phing in a travis environment
5	#
6	# Target system: travis-ci
7	#-----------------------------------------------------------
8	
9	installPearTask ()
10	{
11	    echo -e "\nAuto-discover pear channels and upgrade ..."
12	    pear config-set auto_discover 1
13	    pear -qq channel-update pear.php.net
14	    pear -qq upgrade
15	    echo "... OK"
16	
17	    echo -e "\nInstalling / upgrading phing ... "
18	    which phing >/dev/null                      &&
19	        pear upgrade pear.phing.info/phing ||
20	        pear install --alldeps pear.phing.info/phing
21	
22	    # update paths
23	    phpenv rehash
24	
25	    # re-test for phing:
26	    phing -v 2>&1 >/dev/null    &&
27	        echo "... OK"           ||
28	        return 1
29	}
30	
31	
32	#-----------------------------------------------------------
33	
34	    installPearTask &&
35	        echo -e "\nSUCCESS - PHP ENVIRONMENT READY." ||
36	        ( echo "=== FAILED."; exit 1 )
37	
38	    phing $*
39	
40	#------------------------------------------------------- eof
