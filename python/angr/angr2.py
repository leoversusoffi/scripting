#!/usr/bin/env python3
import angr
import claripy

def main():
  flag_chars = [claripy.BVS('flag_%d' % i, 8) for i in range(32)]
  flag = claripy.Concat(*flag_chars + [claripy.BVV(b'\n')])

  p = angr.Project("angr2")
  st = p.factory.full_init_state(
    args=['./angr2'],
    add_options=angr.options.unicorn,
    stdin=flag,
  )

  for k in flag_chars:
      st.solver.add(k >= 32)
      st.solver.add(k <= 126)

  sm = p.factory.simulation_manager(st)
  sm.explore(find=0x402340, avoid=0x402347)

  return sm.found[0].posix.dumps(0).strip(b'\0\n')

def test():
    assert main().startswith(b'#P(wGomT[$D5^?Q@[QA_*{([.+kCACj')

if __name__ == '__main__':
  print(main())

