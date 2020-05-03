#!/usr/bin/env python3
import pwn
import base64

def new():
  p.sendline(b'1')
  p.sendline(b'aaaaaaaaaaaa')
  p.sendline(b'bbbbbbb')
  ret = p.recvuntil(b'>>')
  ret = ret.split(b'Your token is : \'')[1]
  token = ret.split(b'\'')[0]
  print(b'token: '+token)
  return base64.b64decode(token)

def flip(pad, p2, x):
  ret = b''
  for i in range(0, len(pad)):
    ret += bytes([pad[i] ^ p2[i] ^ x[i]])
  return ret

def login(token):
  p.sendline(b'2')
  p.sendline(token)
  p.recvuntil(b'Hi aaaaaaaaaaaa')
  p.recvuntil(b'>>')

def getFlag():
  p.sendline(b'2')
  ret = p.recvuntil(b'>>')
  ret = ret.split(b'root\n* ')[1]
  ret = ret.split(b'1) Show')[0]
  print(ret)


x   = b';is_member=true'
pad = b'=00000000000000'
p = pwn.remote('host',12345)
p.recvuntil(b'>>')

token = new()
p1 = token[0:16*3]
p2 = token[16*3:16*4]
p3 = token[16*4:16*5]
xor = flip(pad, p2[0:-1], x) + p2[-1:]
payload = base64.b64encode(p1 + p2 + xor + p3)
login(payload)
flag = getFlag()

