{{/*
Expand the name of the chart.
*/}}
{{- define "pilcrow.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "pilcrow.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "pilcrow.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}


{{/*
Common labels
*/}}
{{- define "pilcrow.labels" -}}
helm.sh/chart: {{ include "pilcrow.chart" . }}
{{ include "pilcrow.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "pilcrow.selectorLabels" -}}
app.kubernetes.io/name: {{ include "pilcrow.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "pilcrow.serviceAccountName" -}}
    {{- if .Values.serviceAccount.create }}
        {{- default (include "pilcrow.fullname" .) .Values.serviceAccount.name }}
    {{- else -}}
        {{- default "default" .Values.serviceAccount.name -}}
    {{- end -}}
{{- end -}}


{{/*
Renders a value that contains template perhaps with scope if the scope is present.
Usage:
{{ include "common.tplvalues.render" ( dict "value" .Values.path.to.the.Value "context" $ ) }}
{{ include "common.tplvalues.render" ( dict "value" .Values.path.to.the.Value "context" $ "scope" $app ) }}
*/}}
{{- define "common.tplvalues.render" -}}
{{- $value := typeIs "string" .value | ternary .value (.value | toYaml) }}
{{- if contains "{{" (toJson .value) }}
  {{- if .scope }}
      {{- tpl (cat "{{- with $.RelativeScope -}}" $value "{{- end }}") (merge (dict "RelativeScope" .scope) .context) }}
  {{- else }}
    {{- tpl $value .context }}
  {{- end }}
{{- else }}
    {{- $value }}
{{- end }}
{{- end -}}

{{/*
Return the hostname of the Mysql services
*/}}
{{- define "pilcrow.mysql.host" -}}
  {{ if .Values.mysql.enabled }}
    {{- printf "%s" (include "mysql.primary.fullname" .Subcharts.mysql) }}
  {{- else -}}
    {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.host "context" $) -}}
  {{- end -}}
{{- end -}}

{{/*
Return the port of the Mysql services
*/}}
{{- define "pilcrow.mysql.port" -}}
  {{- if .Values.mysql.enabled -}}
    3306
  {{- else -}}
    {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.port "context" $) -}}
  {{- end -}}
{{- end -}}


{{/*
Return the name of the MySQL secret to use
*/}}
{{- define "pilcrow.mysql.secretName" -}}
    {{- if .Values.mysql.enabled -}}
        {{- printf "%s" (include "mysql.secretName" .Subcharts.mysql) -}}
    {{- else if .Values.pilcrow.mysql.secret.name -}}
        {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.secret.name "context" $) -}}
    {{- else -}}
        {{- printf "%s-mysql" (include "pilcrow.fullname" .) -}}
    {{- end -}}
{{- end -}}

{{/*
Return the key for the MySQL password in the secret
*/}}
{{- define "pilcrow.mysql.passwordKey" -}}
    {{- if .Values.mysql.enabled -}}
        mysql-root-password
    {{- else if .Values.pilcrow.mysql.secret.key -}}
      {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.secret.key "context" $) -}}
    {{- else -}}
        MYSQL_PASSWORD
    {{- end -}}
{{- end -}}

{{/*
Return the name of the MySQL database to use
*/}}
{{- define "pilcrow.mysql.database" -}}
    {{- if .Values.mysql.enabled -}}
      {{- .Values.mysql.auth.database -}}
    {{- else -}}
      {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.database "context" $) -}}
    {{- end -}}
{{- end -}}

{{/*
Return the name of the mysql user to use
*/}}
{{- define "pilcrow.mysql.user" -}}
    {{- if .Values.mysql.enabled -}}
        root
    {{- else -}}
        {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.user "context" $) -}}
    {{- end -}}
{{- end -}}

{{/*
Return the hostname of the Redis service
*/}}
{{- define "pilcrow.redis.host" -}}
  {{- if .Values.redis.enabled -}}
    {{- printf "%s-master" (include "common.names.fullname" .Subcharts.redis) -}}
  {{- else -}}
    {{- include "common.tplvalues.render" (dict "value" .Value.pilcrow.redis.host "context" $) -}}
  {{- end -}}
{{- end -}}

{{/*
Return the port of the Redis service
*/}}
{{- define "pilcrow.redis.port" -}}
  {{- if .Values.redis.enabled -}}
    6379
  {{- else -}}
    {{- include "common.tplvalues.render" (dict "value" (.Values.pilcrow.redis.port | toString) "context" $ ) -}}
  {{- end -}}
{{- end -}}

{{/*
Return the name of the Redis secret to use
*/}}
{{- define "pilcrow.redis.secretName" -}}
    {{- if .Values.redis.enabled -}}
        {{- printf "%s" (include "redis.secretName" .Subcharts.redis) -}}
    {{- else if .Values.pilcrow.redis.secret.name -}}
        {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.redis.secret.name "context" $) -}}
    {{- else -}}
        {{- printf "%s-redis" (include "pilcrow.fullname" .) -}}
    {{- end -}}
{{- end -}}

{{/*
Return the key for the Redis password in the secret
*/}}
{{- define "pilcrow.redis.passwordKey" -}}
{{- if .Values.redis.enabled -}}
    {{- printf "%s" (include "redis.secretPasswordKey" .Subcharts.redis) -}}
{{- else if .Values.pilcrow.redis.secret.key -}}
    {{- include "common.tplvalues.render" (dict "value" .Values.pilrow.redis.secret.key "context" $) -}}
{{- else -}}
    redis-password
{{- end -}}
{{- end -}}


{{/*
Return true if a MySQL secret should be created for external MySQL
*/}}
{{- define "pilcrow.mysql.createSecret" -}}
{{- if and (not .Values.mysql.enabled) (not .Values.pilcrow.mysql.secret) }}
  {{- true -}}
{{- end -}}
{{- end -}}

{{/*
Return true if a Redis secret should be created for external Redis
*/}}
{{- define "pilcrow.redis.createSecret" -}}
{{- if and (not .Values.redis.enabled) (not .Values.pilcrow.redis.secret) }}
  {{- true -}}
{{- end -}}
{{- end -}}


{{/*
Return true if we should create a secret for the appKey
*/}}
{{- define "pilcrow.createAppKeySecret" -}}
{{- if not .Values.pilcrow.appKey.secret -}}
{{- true -}}
{{- end -}}
{{- end -}}

{{/*
Return or generate the appKey
*/}}
{{- define "pilcrow.appKey" -}}
{{- if and (not .Values.pilcrow.appKey.secret) (not .Values.pilcrow.appKey.value) -}}
    {{ randAlphaNum 32 }}
{{- else if .Values.pilcrow.appKey.value -}}
  {{- include "common.tplvaluesrender" (dict "value" .Values.pilcrow.appKey.value "context" $) -}}
{{- end -}}
{{- end -}}


{{/*
Return the name of the appKey secret
*/}}
{{- define "pilcrow.appKeySecretName" -}}
{{- if .Values.pilcrow.appKey.secret -}}
  {{- include "common.tplvaluesrender" (dict "value" .Values.pilcrow.appKey.secret.name "context" $) -}}
{{- else -}}
  {{- printf "%s-appkey" (include "pilcrow.fullname" .) -}}
{{- end -}}
{{- end -}}


{{/*
Return the key fo the appKey in the secret
*/}}
{{- define "pilcrow.appKeySecretKey" -}}
{{- if .Values.pilcrow.appKey.secret -}}
  {{- include "common.tplvaluesrender" (dict "value" .Values.pilcrow.appKey.secret.key "context" $) -}}
{{- else -}}
  {{- "appKey" -}}
{{- end -}}
{{- end -}}

{{/*
Return the cdn base url or null if the cdn is not enabled
*/}}
{{- define "pilcrow.cdnBaseUrl" -}}
{{- if .Values.pilcrow.cdn.enabled -}}
  {{- include "common.tplvaluesrender" (dict "value" .Values.pilcrow.cdn.baseUrl "context" $) -}}
{{- end -}}
{{- end -}}


{{/*
Return the shasums of each of the app configmaps and secrets
*/}}
{{- define "pilcrow.shaSumTemplate" -}}
{{- $ := index . 0 -}}
{{- $template := index . 2 -}}
{{- with index . 1 -}}
{{- include (print $.Template.BasePath $template) . | sha256sum -}}
{{- end -}}
{{- end -}}


{{/*
Return the configuration of the fpm container
*/}}
{{- define "pilcrow.fpmEnv" -}}
envFrom:
- configMapRef:
    name: {{ include "pilcrow.fullname" . }}
env:
  - name: APP_KEY
    valueFrom:
      secretKeyRef:
        name: {{ include "pilcrow.appKeySecretName" . | quote }}
        key: {{ include "pilcrow.appKeySecretKey" . | quote }}
  - name: DB_PASSWORD
    valueFrom:
      secretKeyRef:
        name: {{ include "pilcrow.mysql.secretName" . | quote }}
        key: {{ include "pilcrow.mysql.passwordKey" . | quote }}
  - name: REDIS_PASSWORD
    valueFrom:
      secretKeyRef:
        name: {{ include "pilcrow.redis.secretName" . | quote }}
        key: {{ include "pilcrow.redis.passwordKey" . | quote }}
{{- end -}}
