<?php
/**
 * Ok, glad you are here
 * first we get a config instance, and set the settings
 * $config = HTMLPurifier_Config::createDefault();
 * $config->set('Core.Encoding', $this->config->get('purifier.encoding'));
 * $config->set('Cache.SerializerPath', $this->config->get('purifier.cachePath'));
 * if ( ! $this->config->get('purifier.finalize')) {
 *     $config->autoFinalize = false;
 * }
 * $config->loadArray($this->getConfig());
 *
 * You must NOT delete the default settings
 * anything in settings should be compacted with params that needed to instance HTMLPurifier_Config.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */

return [
    'settings'      => [
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,a[href|title|id|role],ul,ol,li[id],p[style],br,span[style],img[width|height|alt|src],section[role],hr',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
            'Attr.EnableID' => true,
        ],
        'custom_definition' => [
          'id'  => 'html5-definitions',
          'rev' => 1,
          'debug' => false,
      ],
      'custom_attributes' => [
          ['a', 'target', 'Enum#_blank,_self,_target,_top'],
          ['a', 'role', 'Text'],
          ['section', 'role', 'Text'],
      ],
      'custom_elements' => [
          ['u', 'Inline', 'Inline', 'Common'],
          ['section', 'Block', 'Flow', 'Common'],

      ],
        //Admin fields are separated out here b/c we'll likely want to grant admins more leeway in terms of tags/styles in HTML fields
        'admin_fields' => [
            'HTML.Allowed'             => 'div,a[href|title],b,u,i,ul,ol,li,br,p,h2,h3,h4,h5',
            'AutoFormat.RemoveEmpty'   => true,
        ]
    ]
];

