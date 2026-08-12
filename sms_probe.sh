#!/usr/bin/env bash
set -e
URL='http://192.168.100.41:8082'
NUM='+639631371969'
TOKEN='b6e22e03-4fcd-492a-9363-2ee7c0aa7a54'

function probe() {
  local label="$1"
  local url="$2"
  local headers="$3"
  local data="$4"
  echo "--- $label ---"
  curl -s -D - $headers -X POST "$url" -H 'Content-Type: application/json' -d "$data" --max-time 10 | sed -n '1,20p'
  echo
}

probe 'JSON root no token' "$URL/" '' '{"to":"'$NUM'","message":"Test"}'
probe 'JSON root with body token' "$URL/" '' '{"to":"'$NUM'","message":"Test","token":"'$TOKEN'"}'
probe 'JSON /sms with body token' "$URL/sms" '' '{"to":"'$NUM'","message":"Test","token":"'$TOKEN'"}'
probe 'JSON root Authorization Bearer' "$URL/" '-H Authorization: Bearer '$TOKEN'' '{"to":"'$NUM'","message":"Test"}'
probe 'JSON root Auth X-Token' "$URL/" '-H X-Token: '$TOKEN'' '{"to":"'$NUM'","message":"Test"}'
probe 'JSON root Auth Authorization Token' "$URL/" '-H Authorization: Token '$TOKEN'' '{"to":"'$NUM'","message":"Test"}'
probe 'FORM root with token' "$URL/" '' 'to='$NUM'&message=Test&token='$TOKEN''
probe 'FORM /sms with token' "$URL/sms" '' 'to='$NUM'&message=Test&token='$TOKEN''
