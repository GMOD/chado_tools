#!/bin/csh
# chado2acode.sh

set args="-version=3.2.0 -fbgn2id=FBgn2ndary.list -debug"
#  -skip=residues -- if don't want amino,transcript .fasta
set pl="/bio/biodb/common/perl/lib:/bio/biodb/common/system-local/perl/lib"

if ( $#argv < 2 ) then
echo "Convert chado.xml(.gz|bz2) files to flybase acode using perl ChadoSax"
echo "Usage: $0 out.acode in.xml(s) "
echo "Fixed args: $args "
echo "soft from cvs -d ':pserver:anonymous@flybase.net:/bio/cvs' co gmod/schema/XMLTools/ChadoSax"
echo "using perl xml parsers in argos libs: PERL5LIB=$pl"
exit 1
endif

## need current XML/Parser/PerlSAX
if ( ${?PERL5LIB} ) then 
  setenv PERL5LIB "${pl}:${PERL5LIB}"
else
  setenv PERL5LIB $pl
endif

set outf=$1; shift 
if ( -f $outf ) then
echo "output $outf exists; wont overwrite - please remove"
exit 1
endif

if ( -d "./ChadoSax/src" ) then
else
echo "need ChadoSax/src library"
echo "from cvs -d ':pserver:anonymous@flybase.net:/bio/cvs' co gmod/schema/XMLTools/ChadoSax"
exit 1
endif

touch $outf

# new options ToAcode::acode -debug -index -featfile=f -outfile=o
foreach xml ( $* )
perl -I./ChadoSax/src -M'org::gmod::chado::ix::ToAcode' -e 'acode;' -- \
 $args -feat=$outf.feats $xml  >> $outf
end

perl -I./ChadoSax/src -M'org::gmod::chado::ix::ToAcode' -e 'acodeindex;' -- $outf


