tshark -r tshark1.pcap -Y "dns" -T fields -e dns.qry.name | sed 's/-tamu.1e100.net//g'
