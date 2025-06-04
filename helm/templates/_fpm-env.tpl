
{{/*
Return the configuration of the fpm container
*/}}
{{- define "pilcrow.fpmEnv" -}}
envFrom:
- configMapRef:
    name: {{ include "pilcrow.fullname" . }}
env:
  {{- include "pilcrow.secrets.valueFrom" (list "APP_KEY" .Values.pilcrow.appKey $) | nindent 2 }}
  - name: DB_PASSWORD
    valueFrom:
      secretKeyRef:
        {{- include "pilcrow.mysql.secret" . | nindent 8 }}
  - name: REDIS_PASSWORD
    valueFrom:
      secretKeyRef:
        {{- include "pilcrow.redis.secret" . | nindent 8 }}

{{if (eq .Values.pilcrow.mail.driver "smtp") }}
{{- include "pilcrow.secrets.valueFrom" (list "MAIL_PASSWORD" .Values.pilcrow.mail.smtp.password $) | nindent 2}}
{{ else if (eq .Values.pilcrow.mail.driver "ses") }}
    {{ include "pilcrow.secrets.valueFrom" (list "AWS_SECRET_ACCESS_KEY" .Values.pilcrow.mail.ses.password $) }}
  {{- end -}}

{{- end -}}
