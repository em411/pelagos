from __future__ import print_function
import sys
import os
import platform

'''
@author: jvh
'''



class DirectoryTreeNode(object):
    def __init__(self, name, size):
        self.size = size
        self.name = name
        self.children = set()
        self.parent = None

    def __eq__(self, other):
        if other == None:
            return False
        return self.name == other.name

    def __ne__(self, other):
        return not self.__eq__(other)

    def addChild(self,child_node):
        self.children.add(child_node)
        child_node.parent = self
        return child_node

    def getChildren(self):
        return list(self.children)

    def getParent(self):
        return self.parent

    def getSize(self):
        return self.size

    def setSize(self,size):
        self.size = size

    def getName(self):
        return self.name

    def __str__(self):
        return '\nNode: ' + self.name + ', size: ' + self.size

    def find(self,targetName):
        if self.name == targetName:
            return self
        childrenList = self.getChildren()
        for child in childrenList:
            foundNode = child.find(targetName)
            if foundNode != None:
                return foundNode
        return None

    def findInChildren(self,targetName):
        childrenList = self.getChildren()
        for child in childrenList:
            if targetName == child.getName():
                return child
        return None

    def printTree(self,indentations):
        global SPACING
        formatString = '{:75s} [{:>6s}]'
        s = DirectoryTreeNode.getIndentationPrefix(indentations)
        s = s + self.getName()
        DirectoryTreeNode.intToSize(self.getSize())
        print (formatString.format(s,DirectoryTreeNode.intToSize(self.getSize())))
        childrenList = self.getChildren()
        for child in childrenList:
            child.printTree(indentations + 1)
        return

    # Converts bytes into human-readable form, ex TB/GB/MB/KB/Bytes based on size.
    @classmethod
    def intToSize(cls,size):
        if (size >= 10**12):
            return(str(size/10**12) + " TB")
        elif (size >= 10**9):
            return(str(size/10**9) + " GB")
        elif (size >= 10**6):
            return(str(size/10**6) + " MB")
        elif (size >= 10**3):
            return(str(size/10**3) + " KB")
        else:
            return(str(size) + " Bytes")

    #
    #  find this directory path in the tree.
    #  if found return the node in the tree
    #  that represents the end segment of this path
    # i.e.  path is A/B/C/F1 return the F1 node
    # else return None for not found
    pathString = ''

    @classmethod
    def findPathInTree(cls,root,path):
        parts = path.split('/')
        currentNode = root
        global pathString
        pathString = ''
        # for each part in the path find the node in the children of current node

        for part in parts:
            node = currentNode.findInChildren(part)
            if node == None:
                return False
            pathString = pathString + '/' + node.getName()
            currentNode = node
        return True

    @classmethod
    def getIndentationPrefix(cls,indentations):
        if indentations <= 0:
            return ''
        if indentations == 1:
            return StructureDelimiter
        max = indentations - 1
        s = ''
        for n in range(max * SPACING):
            s = s + ' '
        s = s + StructureDelimiter
        return s

    @classmethod
    def buildPathTree(cls,filePaths):
        global pathString
        print("\nBuild Tree")
        global root
        for path in filePaths:
            print(path)
            found = DirectoryTreeNode.findPathInTree(root, path)
            if not found:
                DirectoryTreeNode.buildTree(root,path, root)

    # build a tree for this path with
    # each succesive element a child of the
    # previous node. element 1 of the path
    # must be a child of the root
    @classmethod
    def buildTree(cls,root,path, size):
        parts = path.split('/')
        lastNode = root
        parent = root
        for part in parts:
            child = parent.findInChildren(part)
            if child == None:
                child = DirectoryTreeNode(part, size)
                parent.addChild(child)
            lastNode = parent = child
        lastNode.setSize(size)


root = DirectoryTreeNode('root',0)
StructureDelimiter = '|___'
SPACING = len(StructureDelimiter)
def main():
    filePaths = [
        'Aaaaa/Bbbbb/Ccccc/x1.txt',
        'Aaaaa/Bbbbb/Ccccc/x2.txt',
        'Aaaaa/Bbbbb/Ccccc/x3.txt',
        'Aaaaa/Bbbbb/Ccccc/x4.txt',
        'Aaaaa/Bbbbb/Ccccc/y1.txt',
        'Aaaaa/Bbbbb/y2.txt',
        'Aaaaa/Bbbbb/y3.txt',
        'Aaaaa/Bbbbb/y4.txt',
        'Aaaaa/Bbbbb/Ccccc/Bbbbb/w1.txt',
        'Aaaaa/Bbbbb/Ccccc/Bbbbb/w2.txt',
        'Aaaaa/Bbbbb/Ccccc/Bbbbb/w3.txt',
        'Aaaaa/Bbbbb/Ddddd/Fffff',
        'Aaaaa/Ccccc/z1.txt',
        'Aaaaa/Ccccc/z2.txt',
        'Aaaaa/Ccccc/z3.txt',
        'Aaaaa/Ccccc/z4.txt',
        'Aaaaa/Ooooo']

    DirectoryTreeNode.buildPathTree(filePaths)
    root.printTree(0)

if __name__ == "__main__":
    print('Python ' + platform.python_version())
    if sys.version_info[0] != 2:
        sys.exit('This script ' + os.path.basename(sys.argv[0]) + ' must be run with Python 2')
    sys.exit(main())