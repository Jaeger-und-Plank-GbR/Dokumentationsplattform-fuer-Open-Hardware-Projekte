#!/bin/bash
# Vorschrift ursprünglich André OSEG 
# Vorschrift verändert, übersetzt Andreas Plank

# -------------------------
# Verwendung: Holt einer bestimmten Liste nach MediaWiki-Erweiterungen aus dem Netz und lädt ein bestimmten RELEASE_BRANCH in EXTENSIONS_TARGET_PATH
# Abhängigkeiten: git, sed, awk
# -------------------------

set -Eeuo pipefail
# set -eo pipefail
  # nach https://vaneyckt.io/posts/safer_bash_scripts_with_set_euxo_pipefail/ verändert
  # set -e -- ermöglicht sofortiges enden einer BASH-Vorschrift, falls irgendein Befehl fehlschlägt
  # set -o -- ermöglicht Ende/Abbruch bei unbekannten, unauffindbaren Befehlen, die BASH-Einstellungen auch ausgebend
  # set -u -- ermöglicht daß die BASH-Shell, vergessene unbestimmt Veränderliche (Variablen) als Abbruchfehler zu melden
  # set -x -- ermöglicht daß die BASH jeden Befehl so ausgibt, vor tatsächlicher Ausführung, wie er derzeit Veränderliche enthält; für Entwicklung von Vorschriften geeignet
  # set -E -- ermöglicht Fang-Anweisungen (Traps) auszulösen, wenn eine Vorschrift bestimmte Signale abfängt, abfangen soll. Neben den gebräuchlichen Signalen (z.B. SIGINT, SIGTERM, …) können Anweisungen auch für spezielle Signale wie EXIT, DEBUG, RETURN und ERR aufgewendet werden. Der Leser Kevin Gibbs wies jedoch darauf hin, daß die Anwendung von -e ohne -E dazu führt, daß eine ERR-Fanganweisung in bestimmten Umständen unvermeldet bleibt, daher bevorzugt lieber `set -Ee` einstellen.

farben_bereitstellen() { # allgemein-häufige Verwendung
  # file descriptor [[ -t 2 ]] : 0 → stdin / 1 → stdout / 2 → stderr
  if [[ -t 2 ]] && [[ -z "${ANWEISUNG_FORMAT_FREI:-}" ]] && [[ "${ANWEISUNG_FORMAT:-}" != "stumm" ]]; then
    FORMAT_FREI='\033[0m' ROT='\033[0;31m' GRUEN='\033[0;32m' ORANGE='\033[0;33m' BLAU='\033[0;34m' VEILCHENROT='\033[0;35m' HIMMELBLAU='\033[0;36m' GELB='\033[1;33m'
  else
    FORMAT_FREI='' ROT='' GRUEN='' ORANGE='' BLAU='' VEILCHENROT='' HIMMELBLAU='' GELB=''
  fi
}
farben_bereitstellen

meldung() { # allgemein-häufige Verwendung
  # >&2 ~ Dateischreiber 1 (normales) auf Dateischreiber 2 (Fehler) umleiten
  #   echo >&2 -e "${1-}"
  printf >&2 "%b%s%b\n" "${1:-}"
}

entwicklungs_meldung() { # allgemein-häufige Verwendung
  local meldung; meldung=$1;
  if [[ "${ANWEISUNG_ENTWICKLUNG_VERMELDEN:-}" ]]; then
    meldung "ENTWICKLUNG: $meldung"
  fi
}

abbruch_meldung() { # allgemein-häufige Verwendung
  local meldung; meldung=$1
  local code; code=${2:-1} # Vorgabe Abbruch exit 1
  [[ -z ${meldung// /} ]] \
    && meldung "${ORANGE}Abbruch:${FORMAT_FREI} $meldung" \
    || meldung "${ORANGE}Abbruch${FORMAT_FREI}"
  exit "$code"
}

abhaengigkeiten_pruefen() {
  local stufe_abbruch=0

  if ! [[ -x "$(command -v git)" ]]; then
    printf "${ORANGE}Kommando${FORMAT_FREI} git ${ORANGE} zum Verwalten von Git-Entwicklungsarchiven nicht gefunden: Bitte${FORMAT_FREI} git ${ORANGE}über die Programm-Verwaltung installieren.${FORMAT_FREI}\n"; stufe_abbruch=1;
  fi
  if ! [[ -x "$(command -v sed)" ]]; then
    printf "${ORANGE}Kommando${FORMAT_FREI} sed ${ORANGE}nicht gefunden: Bitte sed über die Programm-Verwaltung installieren.${FORMAT_FREI}\n"; stufe_abbruch=1;
  fi
  if ! [[ -x "$(command -v awk)" ]]; then
    printf "${ORANGE}Kommando${FORMAT_FREI} awk ${ORANGE} zum Textlesen und -verarbeiten nicht gefunden: Bitte${FORMAT_FREI} awk ${ORANGE}über die Programm-Verwaltung installieren.${FORMAT_FREI}\n"; stufe_abbruch=1;
  fi

  case $stufe_abbruch in [1-9]) printf "${ORANGE}(Abbruch)${FORMAT_FREI}\n"; exit 1;; esac
}
abhaengigkeiten_pruefen

letzte_aenderungszeit() { # gesonderte Verwendung
  # letzte Änderungszeit eines Ordners, einer Datei
  local datei_pfad; datei_pfad="$1" # hiesig Veränderliche
  if [[ -e "${datei_pfad}" ]]; then
    stat "$datei_pfad" --printf="%y\n" | sed -r 's@([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2})\..*@\1\2@; s@[-:]@@g;'
  else
    return
  fi
}

installiere_erweiterung_aus_git() { # gesonderte Verwendung: Kernstück der Installation
    # Installiert oder erneuert eine MediaWiki-Erweiterung aus einer Git-Sammlung.
    #
    # Echtwerte (Argumente):
    #   - $1 (erweiterung): Name der Erweiterung
    #   - $2 (git_zweig_o_abfassung): Release version (Git branch or tag). (default: $RELEASE_TAG)
    #   - $3 (git_netzquelle): Git repo URL. (default: https://github.com/wikimedia/mediawiki-extensions-${erweiterung}.git)
    # Anwendung:
    #   - installiere_erweiterung_aus_git UserMerge
    #   - installiere_erweiterung_aus_git UserMerge REL1_37
    # Abhängigkeit:
    #   - letzte_aenderungszeit()
    # nur hiesig Veränderliche
    local erweiterung; local git_zweig_o_abfassung; local git_netzquelle; local zielverzeichnis; 
    
    erweiterung="$1" 
    git_zweig_o_abfassung="${2:-$RELEASE_TAG}"
    git_netzquelle="${3:-https://github.com/wikimedia/mediawiki-extensions-${erweiterung}.git}"
    zielverzeichnis="$VERZ_ERWEITERUNG/$erweiterung"

    if [[ -z ${erweiterung// /} ]];then
      return
    else
      meldung "${GRUEN}${erweiterung} …${FORMAT_FREI}"
    fi
    if [[ -z "${git_zweig_o_abfassung// /}" ]]; then
        meldung "- Erwünschte Git-Abfassung konnte nicht erraten werden (überspringe Erneuerung) …"
        return
    fi
    if [[ -d "$zielverzeichnis" ]]; then
        if [[ ${ANWEISUNG_SICHERN_BEVORZUGEN-} ]];then
          sicherungsverzeichnis=$( printf "%s_%s" "$zielverzeichnis" $(letzte_aenderungszeit "$zielverzeichnis") )
        fi
        if [[ -d "$zielverzeichnis/.git" ]]; then
            meldung "- Prüfe hiesiges Git-Verzeichnis …"
            # check if the origin URL has changed
            local hiesige_git_netzquelle; hiesige_git_netzquelle="$(cd "$zielverzeichnis" && git config --get remote.origin.url)"
            if [[ "$hiesige_git_netzquelle" != "$git_netzquelle" ]]; then
              if [[ ${ANWEISUNG_SICHERN_BEVORZUGEN-} ]];then
                meldung "- Verschiebe sonderliches Git-Verzeichnis … ($(basename "$sicherungsverzeichnis"))"
                mv --interactive "$zielverzeichnis" "$sicherungsverzeichnis"
              else
                meldung "- Lösche sonderliches Git-Verzeichnis …"
                # URL changed -> clean up and get a new copy
                rm --recursive --force "$zielverzeichnis"
              fi
            fi
        else
          # dir exists and is not a Git repository
          meldung "- hiesiges Verzeichnis (kein Git-Verzeichnis) …"
          if [[ ${ANWEISUNG_SICHERN_BEVORZUGEN-} ]];then
            meldung "- Verschiebe alte Erweiterung … ($(basename "$sicherungsverzeichnis"))"
            mv --interactive "$zielverzeichnis" "$sicherungsverzeichnis"
          else
            meldung "- Lösche alte Erweiterung …"
            rm --recursive --force "$zielverzeichnis"
          fi
        fi
    fi

    # Überprüfen, inwieweit erneuerbar die Erweiterung wäre
    # check if extension is already installed and can be updated
    if [[ -d "$zielverzeichnis" ]]; then
        pushd "$zielverzeichnis" >/dev/null
        local bestehender_git_schluessel; bestehender_git_schluessel="$(git rev-parse --verify HEAD)"
        local entfernter_git_schluessel; entfernter_git_schluessel=$( git ls-remote --quiet --refs "$git_netzquelle" "$git_zweig_o_abfassung" | cut --fields=1 )
        if [[ -z ${entfernter_git_schluessel// /} ]]; then
            meldung "- Bestehende Erweiterung wird übersprungen (entfernte Fassung unbekannt ~ “master” o.ä. bitte selbst nachforschen)"
        elif [[ "$bestehender_git_schluessel" != "$entfernter_git_schluessel" ]]; then
            meldung "- Bestehende Erweiterung wird zu $entfernter_git_schluessel erneuert, vermittels git fetch & checkout …"
            git fetch --recurse-submodules --jobs=8 --depth 1 --quiet origin "$git_zweig_o_abfassung" 
            git checkout --quiet "$entfernter_git_schluessel"
        else
            meldung "- Bestehende Erweiterung wird übersprungen (nichts zu erneuern, hiesige Fassung & Fremdfassung: $entfernter_git_schluessel)"
        fi
        popd >/dev/null
        return
    fi

    # get a fresh copy of the extension
    # eine neue Ausfertigung der Erweiterung erhalten
    meldung "- Erweiterung ganz neu erstellen (Fassung $git_zweig_o_abfassung)"
    git clone --recurse-submodules --jobs=8 --branch "$git_zweig_o_abfassung" --depth 1 --quiet "$git_netzquelle" "$zielverzeichnis"
    return
}

errate_hiesige_mw_gitfassung() { # gesonderte Verwendung
    local bestehende_mw_fassung;
    bestehende_mw_fassung=$( grep 'MW_VERSION' "${VERZ_MW_INSTALLATION}/includes/Defines.php" | grep --extended-regexp --only-matching '[0-9\.]+' )
    if [[ -z "${bestehende_mw_fassung// /}" ]]; then
        # Informationen zur alten Fassung
        bestehende_mw_fassung=$( grep 'wgVersion' "${VERZ_MW_INSTALLATION}/includes/DefaultSettings.php" | grep --extended-regexp --only-matching '[0-9\.]+' )
    fi
    echo -n "$bestehende_mw_fassung" | sed --regexp-extended "s@(\d+)\.(\d+).*@REL\1_\2@"
}


# allgültig VERÄNDERLICHE
ANWEISUNG_SICHERN_BEVORZUGEN="ja" # irgendein Wert, oder leer lassen
ANWEISUNG_ENTWICKLUNG_VERMELDEN="" # irgendein Wert, oder leer lassen

VERZ_MW_INSTALLATION=/pfad/zum/vhosts/wiki/wgScriptPath
VERZ_ERWEITERUNG="${VERZ_MW_INSTALLATION}/extensions"
RELEASE_TAG=$(errate_hiesige_mw_gitfassung)
RELEASE_TAG=REL1_38



# ------------------------------------------------------------------------------
# Hauptteil
# ------------------------------------------------------------------------------
# comm -23  Liste_Erweiterungen_en.oho.wiki_MW_1_31.txt Liste_Erweiterungen_MW_1_35_10.txt


# veroeffentlichte_erweiterungen=(
#   Erweiterung
#   Erweiterung HEAD
#   Erweiterung specialTAG
#   # kommentierte Zeile
#   Erweiterung specialTAG https://git_netzquelle/spezial/Erweiterung.git
# )
e=$( cat <<ERWEITERUNGEN
   # Bootstrap composer 4.5.0
   EmbedVideo v2.9.0 https://gitlab.com/hydrawiki/extensions/EmbedVideo.git
   # EmbedVideo https://gitlab.com/hydrawiki/extensions/EmbedVideo/-/archive/v2.9.0/EmbedVideo-v2.9.0.tar.gz scheint diesselbe
   # googleAnalytics ws. veraltet, Empfehlung ist Extension:HeadScript anzustellen
   # SemanticMediaWiki     composer
   # SemanticResultFormats composer
   AJAXPoll
   Arrays
   Comments
   CommentStreams
   ContactPage
   CookieWarning
   DiscussionThreading
   HeaderTabs
   MassEditRegex
   Moderation master   https://github.com/edwardspec/mediawiki-moderation.git
   MsUpload
   # PageForms composer
   PageExchange
   PipeEscape
   SocialProfile
   Variables
   WikiSEO
ERWEITERUNGEN
)

NORMAL_IFS=$' \t\n'; 
IFS=$'\n' # einzig Zeilenumbrüche (\n) als Feldtrenner
readarray -t E < <(echo "$e")
IFS=$NORMAL_IFS

# Datenfeld (array) aus Zeilenumbrüchen erstellen und Kommentarzeilen entfernen
for zaehler in "${!E[@]}"; do 
  kommentarzeile="^ +# [\n]*";
  leerzeile="^ *$";
  if   [[ ${E[zaehler]} =~ $kommentarzeile ]]; then
    unset 'E[zaehler]'
  elif [[ ${E[zaehler]} =~ $leerzeile ]]; then
    unset 'E[zaehler]'
  fi
done
VEROEFFENTLICHTE_ERWEITERUNGEN=("${E[@]}")

LISTE_ERWEITERUNGEN=$( printf '%s\n' "${VEROEFFENTLICHTE_ERWEITERUNGEN[@]}" \
  | awk ' { print $1 }' | tr "\n" " " | fold --spaces \
  | sed "s@^@  @g" )
  
vorbemerkungen=$( cat <<VORBEMERKUNG
--------------------------------------------
 Erweiterung MediaWikis ($RELEASE_TAG) erneuern
--------------------------------------------

Erstelle oder erneuere MediaWiki-Erweiterungen …
- in $VERZ_MW_INSTALLATION
- Zielverzeichnis ${VERZ_ERWEITERUNG}
- Git-Verzeichnisse werden in sich erneuert\n
VORBEMERKUNG
)

if [[ ${ANWEISUNG_SICHERN_BEVORZUGEN:-} ]]; then
  vorbemerkungen="${vorbemerkungen}- Git-ungleiche Verzeichnisse werden gesichert und verschoben" 
  else
  vorbemerkungen="${vorbemerkungen}- Git-ungleiche Verzeichnisse ${ORANGE}werden gelöscht${FORMAT_FREI}"
fi

vorbemerkungen=$( cat <<VORBEMERKUNG
$vorbemerkungen

Zu bedenken ist, daß einige Erweiterungen vermittels composer-System geeigneter einzupflegen wären:
- siehe MW-Verzeichnis ${BLAU}composer.local.json${FORMAT_FREI}
- siehe Dokumentation ${BLAU}https://www.mediawiki.org/wiki/Composer/de${FORMAT_FREI}

Nun die Erweiterungen …${BLAU}
${LISTE_ERWEITERUNGEN}
${FORMAT_FREI}… auf $RELEASE_TAG erneuern? (ja/NEIN)
VORBEMERKUNG
)

meldung "$vorbemerkungen"

if ! [[ -w "${VERZ_ERWEITERUNG}" ]] ; then 
  meldung "${ORANGE}Aber wir können so nicht hineinschreiben: Die ${FORMAT_FREI}Schreibrechte${ORANGE} müssen richtig gesetzt sein …${FORMAT_FREI}" ;
   stat $VERZ_ERWEITERUNG ;
  abbruch_meldung "Schluß hier." ;
fi

# Anfrage/Aufforderung in Befehlszeile (prompt)
read -r janein_antwort
if [[ -z ${janein_antwort// /} ]];then janein_antwort="nein"; fi
case $janein_antwort in
  [jJ]|[jJ][aA])
    meldung "${GRUEN}Ja ~ Weiter ...${FORMAT_FREI}"
  ;;
  [nN]|[nN][eE][iI][nN])
    abbruch_meldung "Nein";
  ;;
  *) 
    if [[ -z ${janein_antwort// /} ]];then
      abbruch_meldung "Antwort enthielt „leer“"
    else
      abbruch_meldung "Eingabe nicht (als ja oder nein) erkannt „${janein_antwort}“"
    fi
  ;;
esac

for (( zaehler=0; zaehler<${#VEROEFFENTLICHTE_ERWEITERUNGEN[@]}; zaehler++ ))
do
  Lesezeile=${VEROEFFENTLICHTE_ERWEITERUNGEN[$zaehler]}
  diese_erweiterung=$( echo "$Lesezeile" | awk ' { print $1 }' )
  diese_sonder_gitfassung=$( echo "$Lesezeile" | awk ' { print $2 }' )
  diese_sonder_gitnetzquelle=$( echo "$Lesezeile" | awk ' { print $3 }' )
  
  entwicklungs_meldung "$zaehler ~ e:$diese_erweiterung f:$diese_sonder_gitfassung n:$diese_sonder_gitnetzquelle"
  if [[ -z ${diese_sonder_gitfassung// /}  ]]; then
    entwicklungs_meldung "$( printf "installiere_erweiterung_aus_git \"%s\"\n" "$diese_erweiterung" )";
    installiere_erweiterung_aus_git "$diese_erweiterung";
  else
    if [[ -z ${diese_sonder_gitnetzquelle// /}  ]]; then
      entwicklungs_meldung "$( printf "installiere_erweiterung_aus_git \"%s\" \"%s\"\n" "$diese_erweiterung" "$diese_sonder_gitfassung" )";
      installiere_erweiterung_aus_git "$diese_erweiterung" "$diese_sonder_gitfassung";
    else
      entwicklungs_meldung "$( printf "installiere_erweiterung_aus_git \"%s\" \"%s\" \"%s\"\n" "$diese_erweiterung" "$diese_sonder_gitfassung" "$diese_sonder_gitnetzquelle" )";
      installiere_erweiterung_aus_git "$diese_erweiterung" "$diese_sonder_gitfassung" "$diese_sonder_gitnetzquelle";
    fi
  fi
done
IFS=$NORMAL_IFS

meldung "${GRUEN}Fertig:${FORMAT_FREI} Überarbeitung der Erweiterungen abgeschlossen"
