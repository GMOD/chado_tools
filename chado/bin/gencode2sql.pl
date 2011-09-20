#!/usr/bin/perl -w
#This is old code and hasn't been tested!
use strict;

my @nucs = qw(T C A G);
my $x = 0;
my @codons = ();
for my $i (@nucs) {
    for my $j (@nucs) {
        for my $k (@nucs) {
            my $codon = "$i$j$k";
            $codons[$x] = $codon;
            $x++;
        }
    }
}

print "-- autogenerated by gencode2sql.pl from NCBI gencode.dmp\n";
print "SET search_path=public,genetic_code;\n";

my @rows=();
while(<>) {
    chomp;
    push(@rows, [split(/\s*\|\s*/,$_)]);
}
foreach (@rows) {
    my ($id,$x,$n,$code,$starts) = @$_;
    printf("INSERT INTO gencode (gencode_id,organismstr) VALUES (%d,%s);\n", $id, pquote($n));
}
foreach (@rows) {
    my ($id,$x,$n,$code,$starts) = @$_;
    next unless $id;
    my @codes = split('',$code);
    for (my $i=0;$i<64;$i++) {
        printf("INSERT INTO gencode_codon_aa (gencode_id,codon,aa) VALUES (%d,%s,%s);\n", 
               $id,
               pquote($codons[$i]),
               pquote($codes[$i]));
    }
}
foreach (@rows) {
    my ($id,$x,$n,$code,$start) = @$_;
    next unless $id;
    my @starts = split('',$start);
    for (my $i=0;$i<64;$i++) {
        printf("INSERT INTO gencode_startcodon (gencode_id,codon) VALUES (%d,%s);\n", 
               $id,
               pquote($codons[$i]))
          if $starts[$i] eq 'M';
    }
}

sub pquote {
    my $s = shift;
    $s='' unless defined $s;
    if ($s =~ /^\-?[0-9]+$/) {
	return $s;
    }
    return "''" unless $s;
    $s =~ s/\'/\'\'/g;
    "'$s'";
}

