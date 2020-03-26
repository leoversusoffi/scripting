#! /usr/bin/env python3
import angr

def main():
    p = angr.Project("angr1")
    simgr = p.factory.simulation_manager(p.factory.full_init_state())
    simgr.explore(find=0x400844)

    return simgr.found[0].posix.dumps(0).strip(b'\0\n')

def test():
    assert main().startswith(b'Code_Talkers')

if __name__ == '__main__':
    print(main())
