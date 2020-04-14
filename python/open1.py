#!/usr/bin/env python3
r = ''
with open("out1","r") as f:
 with open("out2","r") as g:
  t1 = f.readlines()
  t2 = g.readlines()
  for i in range(0,86553):
   if ')' in t2[i].rstrip():
    r += chr(int(t1[i].rstrip(),16))
   else:
    a = int(t1[i].rstrip(),16)
    b = int(t2[i].rstrip(),16)
    c = a ^ b
    r += chr(c)

print(r)
