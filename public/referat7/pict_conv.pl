#!/usr/bin/perl -w

# ber_conv.pl Berichtseingabe, Konvertieren aller Bilder des SOURCE_PATH
#               die convertierten Bilder werden im TARG_PATH abgelegt.

use CGI qw/ :standard/  ;

use File::Spec;
use Text::ParseWords;
use Getopt::Std;
use Image::Magick;

# initial variables

$title    = "Berichtseingabe, Konvertieren der Bilder, Texteingabe" ;

$log_file = "pict_conv_pl.log";
$debug = "print";

$ltime = localtime(time);

$twidth = 340 ;    # Bild- Breite

 open (LOG, ">>$log_file" );

# $SOURCE_PATH = "\/daten\/canon530d\/2006_09_03";
$SOURCE_PATH = "d:\\daten\\canon530d\\2006_09_03";
# $SOURCE_PATH = "..\\..\\canon530d\\DCIM\\134CANON\\2006_09_03";
# $TARG_PATH   = "d:\\daten\\www\\ffhistnoe\\referat7\\firetr_2006";
$TARG_PATH   = "firetr_2006";


  opendir DIR, $SOURCE_PATH || h3("Foto- Ordner $SOURCE_PATH kann nicht ge&ouml;ffnet werden $! \n") ;
  my @dots =  readdir(DIR);
  closedir DIR;

  my $foto_src = '' ;
  my $foto_targ = '' ;
  my $bz = 0;
  my $align = "left";

  my  $b=0;

  my $dirlen = scalar (@dots);

#  for ($i=2; $i <= $dirlen; $i++ ) {
  for ($i=2; $i <= 5; $i++ ) {

    if ($debug eq "print") {
      print LOG "$ltime ber_conv: Schleife Bilder: Cnt $i, \n" ;
    }


      if ($debug eq "print") {
        print LOG "$ltime ber_conv: Schleife Bilder: Bericht = JA \n" ;
      }

      $bz++;

      $foto_src = "$SOURCE_PATH\\$dots[$i]" ;
print "\$foto_src $foto_src\n";
      $foto_targ = "$TARG_PATH\\bild$i.jpg" ;

      if ($debug eq "print") {
        print LOG "$ltime ber_conv: \n\t fotosrc: $foto_src, \n\t  foto_targ: $foto_targ \n" ;
      }

      print "Bild $bz wird bearbeitet \n" ;

      convert ($foto_src, $foto_targ, $twidth, "schas") ;

      if ($debug eq "print") {
        print "Bild $bz wurde bearbeitet \n\n" ;
      }

  }


  close(LOG);


$ltime = localtime(time);

open (LOG, ">>$log_file" );
print LOG "$ltime ber_conv ended \n" ;
close(LOG) ;



sub convert {

  my $ArgSrcFile = $_[0] ;
  my $ArgTargFile = $_[1] ;
  my $ArgTargLongSide = $_[2] ;
  my $ArgTargBerArt   = $_[3] ;
  my $Image ;
  my $Width;
  my $Height;
  my $TargetWidth;
  my $TargetHeight;
  my $Rc;

  my $AnnotText     = '(C) Feuerwehrhistoriker in NOe, Josef Rohowsky';  #
  my $ImageFilter   = 'Lanczos';                # Do not change below unless you know what you
  my $ImageBlur     = 0.8;                      #
#  my $AnnotFont     = 'Comic-Sans-MS-Bold';     #
  my $AnnotFont     = 'Arial';             #
  my $AnnotFontSize = 16;                       #
  my $Radius        = 10;                       #


  $Image = Image::Magick->new;
  $Rc = $Image->Read($ArgSrcFile);
  if ($Rc)
  {
    print "BER_CONV: $Rc\n";
    exit(1);
  }

  $Width = $Image->Get('columns');
  $Height = $Image->Get('rows');

  if ( $ArgTargBerArt eq "Brandschutz" || $ArgTargBerArt eq "Jugend") {
    $TargetWidth = $ArgTargLongSide;
  }
  else {
    if ($Height >= $Width) {
      $TargetWidth = $ArgTargLongSide/1.2;
    }
    else {
      $TargetWidth = $ArgTargLongSide;
    }
  }
  $TargetHeight = int($Height*$TargetWidth/$Width);
  $Rc = $Image->Resize(width=>$TargetWidth,
                       height=>$TargetHeight,
                       filter=>$ImageFilter,
                       blur=>$ImageBlur);
  if ($Rc)
  {
    print "BER_CONV: $Rc\n";
    exit(1);
  }
  $Rc = $Image->Sharpen(radius=>$Radius,sigma=>0.8);
  if ($Rc)
  {
    print "BER_CONV: $Rc\n";
    exit(1);
  }
  if ($AnnotText)
  {
    $Rc = $Image->Annotate(font=>$AnnotFont,
                           pointsize=>$AnnotFontSize,
                           fill=>'black',
                           x=>10,
                           y=>$TargetHeight-10,
                           text=>$AnnotText);
    if ($Rc)
    {
      print "BER_CONV: $Rc\n";
      exit(1);
    }
    $Rc = $Image->Annotate(font=>$AnnotFont,
                           pointsize=>$AnnotFontSize,
                           fill=>'white',
                           x=>11,
                           y=>$TargetHeight-11,
                           text=>$AnnotText);
    if ($Rc)
    {
      print "BER_CONV: $Rc\n";
      exit(1);
    }
  }
  $Rc = $Image->Profile(name=>"*");
  if ($Rc)
  {
    print "BER_CONV: $Rc\n";
    exit(1);
  }
  $Rc = $Image->Write(filename=>$ArgTargFile);
  if ($Rc)
  {
    print "BER_CONV: $Rc\n";
    exit(1);
  }


}



