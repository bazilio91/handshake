#!/usr/bin/perl

# Mysql external auth script
# Features: auth and isUser work, but setPass doesn't.
# Restrictions: Username or passwords may not contain some special characters: $'"` nor line breaks

# Security considerations:
#  - i am not sure whether password is shown in the "echo" sentence when listing processes, perhaps not if echo is a shell builtin
#  - character filtering may not be perfect, but the most important '$"` are filtered out by this script
#  - mysql user password should not be set on command-line, instead use --defaults-extra-file=... The file must contain [client] in the first line and password=... next (check some man page for more details)
#

# 2005-1-24 Modified by Alejandro Grijalba (SuD) http://www.latinsud.com
# Based on check_pass_null.pl script

my $domain = $ARGV[0] || "example.com";

while(1)
  {
   # my $rin = '',$rout;
   # vec($rin,fileno(STDIN),1) = 1;
   # $ein = $rin;
   # my $nfound = select($rout=$rin,undef,undef,undef);

    my $buf = "";
    my $nread = sysread STDIN,$buf,2;
    do { exit; } unless $nread == 2;
    my $len = unpack "n",$buf;
    my $nread = sysread STDIN,$buf,$len;

    my $out = pack "nn",2,1;
    syswrite STDOUT,$out;
  }

closelog;