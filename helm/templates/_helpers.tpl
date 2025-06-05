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
    {{- $host := include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.host "context" $) -}}
    {{- if not (eq $host "null") -}}
    {{- $host -}}
    {{- end -}}
  {{- end -}}
{{- end -}}

{{/*
Return the hostname of the Redis service
*/}}
{{- define "pilcrow.redis.host" -}}
  {{- if .Values.redis.enabled -}}
    {{- printf "%s-master" (include "common.names.fullname" .Subcharts.redis) -}}
  {{- else -}}
    {{- $host := include "common.tplvalues.render" (dict "value" .Values.pilcrow.redis.host "context" $) -}}
    {{- if not (eq $host "null") -}}
    {{- $host -}}
    {{- end -}}
  {{- end -}}
{{- end -}}


{{/*
Return the name of the MySQL secret to use
*/}}
{{- define "pilcrow.mysql.secret" -}}
{{- if .Values.mysql.enabled -}}
name: {{ printf "%s" (include "mysql.secretName" .Subcharts.mysql) }}
key: mysql-root-password
{{- else if (.Values.pilcrow.mysql.password.secret).name -}}
name: {{ include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.password.secret.name "context" $) }}
key: {{ include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.password.secret.key "context" $) }}
{{- else -}}
name: {{ include "pilcrow.secrets.defaultName" . }}
key: DB_PASSWORD
{{- end -}}
{{- end -}}

{{/*
Return the name of the MySQL secret to use
*/}}
{{- define "pilcrow.redis.secret" -}}
{{- if .Values.redis.enabled -}}
name: {{ printf "%s" (include "redis.secretName" .Subcharts.redis) }}
key: {{ printf "%s" (include "redis.secretPasswordKey" .Subcharts.redis) }}
{{ else if (.Values.pilcrow.redis.password.secret).name -}}
name: {{ include "common.tplvalues.render" (dict "value" .Values.pilcrow.redis.password.secret.name "context" $) }}
key: {{ include "common.tplvalues.render" (dict "value" .Values.pilcrow.redis.password.secret.key "context" $) }}
{{- else -}}
name: {{ include "pilcrow.secrets.defaultName" . }}
key: REDIS_PASSWORD
{{- end -}}
{{- end -}}

{{/*
Return the name of the default secret resource
*/}}
{{- define "pilcrow.secrets.defaultName" -}}
{{- include "pilcrow.fullname" . | printf "%s-secrets" -}}
{{- end -}}

{{/*
Return the name of the MySQL database to use
*/}}
{{- define "pilcrow.mysql.database" -}}
{{- .Values.mysql.enabled | ternary .Values.mysql.auth.database (include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.database "context" $)) -}}
{{- end -}}

{{/*
Return the name of the mysql user to use
*/}}
{{- define "pilcrow.mysql.user" -}}
{{- .Values.mysql.enabled | ternary "root" (include "common.tplvalues.render" (dict "value" .Values.pilcrow.mysql.user "context" $)) -}}
{{- end -}}

{{/*
Return the cdn base url or null if the cdn is not enabled
*/}}
{{- define "pilcrow.cdnBaseUrl" -}}
{{- if .Values.pilcrow.cdn.enabled -}}
  {{- include "common.tplvalues.render" (dict "value" .Values.pilcrow.cdn.baseUrl "context" $) -}}
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
usage {{ include "pilcrow.valueFromSecret ("MAIL_PASSWORD" .Values.pilcrow.mysql.password $) }}
 */}}
{{- define "pilcrow.secrets.valueFrom" -}}
{{- $envName := index . 0 -}}
{{- $value := index . 1 -}}
{{- $context := index . 2 -}}
- name: {{ $envName }}
  valueFrom:
    secretKeyRef:
      name: {{ (($value).secret).name | default (printf "%s-secrets" (include "pilcrow.fullname" $context)) | quote }}
      key: {{ (($value).secret).key | default $envName | quote }}
{{- end -}}

{{/*
Return the value of a secret key if it exsists
Usage: {{ include "pilcrow.secrets.generate" (dict "key" "pilcrow.mail.smtp.password" "env" "MAIL_PASSWORD")}}
*/}}
{{- define "pilcrow.secrets.generate" -}}
{{- $envName := index . 0 -}}
{{- $value := index . 1 -}}
{{- $context := index . 2 -}}
{{- if not (and $value.secret ($value.secret).name) -}}
{{ $envName }}: {{ include "common.tplvalues.render" (dict "value" $value.value "context" $context) | b64enc }}
{{ end }}
{{- end -}}
