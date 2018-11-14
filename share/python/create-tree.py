#!/usr/bin/python
# -*- coding: utf-8 -*-

import csv
import getopt
from collections import OrderedDict
import os
import re
import sys

def check_header(filename):
    # This function checks for a valid hashdeep header
    # in the passed file. If the header is valid, the
    # invocation path is returned, otherwise false
    # is returned in the event of an invalid header.

    # example header:

    # %%%% HASHDEEP-1.0
    # %%%% size,md5,sha256,filename
    # ## Invoked from: /mnt/LTFS/R1.x137.108.0001
    # ## $ hashdeep -r .
    # ##

    with open(filename) as f:
        first = f.readline().rstrip() == '%%%% HASHDEEP-1.0'
        second = f.readline().rstrip() == '%%%% size,md5,sha256,filename'
        third_line = f.readline().rstrip()
        third = re.match('^## Invoked from: ', third_line) != None
        fourth_line = f.readline().rstrip()
        # This hardcoded offset is safe because of the previous re.match() check.
        path = third_line[17:]
        forth = re.match('^## \$ hashdeep -r ', fourth_line) != None
        fifth = f.readline().rstrip() == '##'
    if (first and second and third and forth and fifth):
        return path
    else:
        return None

# https://www.oreilly.com/library/view/python-cookbook/0596001673/ch04s16.html
def splitall(path):
    allparts = []
    while 1:
        parts = os.path.split(path)
        if parts[0] == path:  # sentinel for absolute paths
            allparts.insert(0, parts[0])
            break
        elif parts[1] == path: # sentinel for relative paths
            allparts.insert(0, parts[1])
            break
        else:
            path = parts[0]
            allparts.insert(0, parts[1])
    return allparts

def generate_tree(filename, short):
    path = check_header(filename)
    if (path is not None):
        sizes = OrderedDict()
        with open(filename, 'rb') as f:
            reader = csv.reader(f)
            rownum = 1
            last = None
            for row in reader:
                # skip header
                if (rownum > 5):
                    object_filename = re.sub(path + '/', '', row[3])
                    object_size = row[0]
                    parts = splitall(object_filename)
                    for i in range (0, len(parts)-1, 1):
                        if (i == 0):
                            my_str = parts[i]
                        else:
                            my_str = my_str + '/' + parts[i]
                        try:
                            sizes[my_str] += int(object_size)
                        except KeyError:
                            sizes[my_str] = int(object_size)
                rownum += 1
            # ASCII-art generation
            last_path = ''
            for path, size in sizes.iteritems():
                if (short):
                    # Short form, directories only
                    depth = 0
                    if (path.startswith(last_path) and last_path != ''):
                        depth += 1
                        remaining_path = re.sub(last_path, '', path)
                        spaces = ''
                        for s in range (1, len(last_path), 1):
                            spaces = spaces + ' '
                        remaining_path = re.sub('/', '\n' + spaces + '└──', path)
                        for d in range (1, depth, 1):
                            print "    ",
                        print(remaining_path)
                    else:
                        print path
                    last_path = path
                else:
                    # Long Form
                    if (path.startswith(last_path)):
                        print path, size
                    else:
                        print path, size
                    last_path = path
    else:
        print("Error in header. Stopping")

def main(argv, script_name):
    hashfile = ''
    short_report = False;
    try:
        opts, args = getopt.getopt(argv,"hdi:",["ifile=",])
    except getopt.GetoptError:
        print script_name + ' -i <hashfile>'
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print script_name + ' -i <hashfile> [-d <show directories only>]'
            sys.exit()
        elif opt in ("-i", "--ifile"):
            hashfile = arg
        elif opt in ("-d"):
            short_report = True
    generate_tree(hashfile, short_report)

if __name__ == "__main__":
    main(sys.argv[1:], sys.argv[0])

