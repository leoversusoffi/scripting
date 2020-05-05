import itertools, math
 
ct = "GXGXGVVXAXADVGGVXAVXXXAVDGXXDAFXGVDXGVAXVVVXXDGAAVAXGVDXAXAXGXVXFXAGVVVDVXGVAGGGVGAXXXXXDVDXDXXVVXXFXVXFFADAVXFXGXVXXVAXXXADAXVAGVGGGAGAGXAXFVVXVAFXGXVXGAXXXFFXAVVVFGFDGADFGDXAGXAGGDAXGXGXGAVAGXVXGVGGVXAVGXGXGVFVGXGAAGAFGXXVGFGXVXVXGAVDADFDAAXGXGXVXGXGXXXGVGGDXVGAXXXXXVGGVVXAVGXGFFDFGGGVXXFGDGDVXDGDAFDGXFVVGXGXXGVXVGDVXDGAGVDVGDDXXAFADAVGFVXAVGGDXAXXGGVAAXVAXGVFXFXADXXVXFVGDGAGGXXFXGXVXXVGVAAXXVXXGXGFDADAGXAXDGGXGXAXGXGDDXGGGGGXXVAXGVDXGAFVGGVGXXGDAXVVFXAAAVXGAXXAVXAXVXDGVDXFFDXAAVXFAVXGVAVVVGDADDFXXDVGAAXXXGGVDXVAXXVAXXAGVVAGAXXVXGDAGAXVVGXAGGXXVGVGADAGVXXGGGXGDFVXAFDGVGXGDVXVXVXVVFGVAXFGDXAGGXXAFVXVAGGAFFXVXGXAXAFVAGAFAAVFFDDAXVXAAFXVXGXVGAXGXXAXAVXDXXXDFXXFGXADGXXFGXAGVFXFXGDAXDXAGVXFXVFADAXGAAXADVVFXX"

def count_bigrams(text):
        pairs=[]
        for i in range(0,len(text),2):
                if (text[i:i+2]) not in pairs:
                        pairs.append(text[i:i+2])
        return len(pairs)
 
def get_iters(keylength):
        l = []
        l.extend(range(keylength))
        return ([x for x in itertools.permutations(l)])
 
def transpose(text, keylength, iteration):
        table = []
        pt = ''
        numzeros = int(math.ceil(len(text) / float(keylength)))
        for i in range(keylength):
                table.append([0] * numzeros)
        erasures = (numzeros * keylength) - len(text)
        for i in xrange(erasures):
                del table[i + (keylength - erasures)][0]
        for i in range(len(iteration)):
                table[iteration.index(i)] = list(text[0:len(table[iteration.index(i)])])
                text = text[len(table[iteration.index(i)]):]
        for row in range(len(table[0])):
                for column in range(len(table)):
                        try:
                                pt += table[column][row]
                        except IndexError:
                                pass
        return pt
 
def simplify(text):
        groups = []
        pt = ''
        for i in range(0, len(text), 2):
                group = text[i:i+2]
                if group not in groups:
                        groups.append(group)
                pt += ab[groups.index(group)]
        return pt
 
ab = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
counts = []
keylength = 9
print(keylength)
print()
iterations = get_iters(keylength)
for iteration in iterations:
        pt = transpose(ct, keylength, iteration)
        count = count_bigrams(pt)
        if count < 32:
                print()
                print(count)
                print()
                print(pt)
                print()
                npt = simplify(pt)
                print(npt)
                print(iteration)
