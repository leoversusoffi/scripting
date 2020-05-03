#!/usr/bin/python

### http://bma.bozhu.me/
# Polynomial  : x^16 + x^15 + x^13 + x^5 + 1
# Linear span : 16

def getNext(s):
  n = s[0] ^ s[5] ^ s[13] ^ s[15]
  return n

seed = [1,1,0,0,1,0,1,0,1,1,1,1,1,1,1,0]

r = ''
for i in range(0, 214899):
  s = ''
  for i in range(0,8):
    n = getNext(seed)
    s += str(seed.pop(0))
    seed.append(n)
  r += "%02x" % int(str(s),2)

print(r)

