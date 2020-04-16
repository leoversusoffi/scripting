from pngparser import PngParser

png = PngParser('puzzled.png')
idx = 5
order = [2, 0, 1, 3, 6, 10, 8, 7, 9, 4, 5]
chunk = [0] * 11

i = 0
for j in order:
  chunk[j] = png.chunks[i + idx]
  i = i + 1

png.chunks = png.chunks[:idx] + chunk + png.chunks[-1:]
png.save_file('cleaned.png')
