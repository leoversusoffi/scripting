tshark -r tshark2.pcap -Y "ip.src==192.168.56.101"  -Tfields -e dns.qry.name | sed 's/.jz-n-bs.local//'  | tr -d "." | cut -b 19- | xxd -r -p | tail -c +9 > tshark2.png
