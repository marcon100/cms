<?php
/**
 * Table Helper
 *
 * PHP version 5
 *
 * @package  QuickApps.View.Helper
 * @version  1.0
 * @author   Christopher Castro <chris@quickapps.es>
 * @link     http://www.quickappscms.org
 */

/**
 * ## Expected data's structure
 * $data MUST be a numeric array. (Any list result of `Model::find()` or paginated result)
 * {{{
 *  $data = array(
 *      0 => array(
 *          'Model' => array('field1' => 'data', ...),
 *          'Model2' => ...
 *      ),
 *       ....
 *  );
 * }}}
 *
 * ## Options:
 * columns (array): Information about each of the columns of your table.
 * {{{
 *  ...
 *  'Column Title' => array(
 *      'value' => ,        (string) Values to display when filling this column.
 *                            You can specify array paths to find in the $data array. e.g.: `{Model.field}`
 *                            See TableHelper::_renderCell() for more tags.
 *      'thOptions' => ,    (array) <th> tag options for this column. This will affect table header only.
 *      'tdOptions' => ,    (array) <td> tag options for this column. This will affect table body (result rows) only.
 *      'sort' =>           Optional (string) `Model.field`
 *  )
 *  ...
 * }}}
 *
 */
class TableHelper extends AppHelper {
/**
 * Helpers used by TableHelper.
 *
 * @var array
 */
    public $helpers = array('Html', 'Paginator');

/**
 * Default table rendering options.
 * `columns` (array): Settings for each table's column. (see TableHelper::$__columnDefaults).
 * `headerPosition` (mixed): Column titles position. 'top', 'top&bottom', bottom'. Or (boolean) FALSE for no titles.
 * `noItemsMessage` (string): Message to show if there are no rows to display.
 * `tableOptions` (array): <table> tag attributes.
 * `trOptions` (array): <tr> tag attributes for every row of content (between <tbody></tbody>).
 * `paginate.position` (string): Pagination row position, 'top' or 'top&bottom' or 'bottom'.
 * `paginate.trOptions` (array): <tr> tags attributes.
 * `paginate.tdOptions` (array): <td> tags attributes.
 *
 * @var array
 */
    private $__defaults = array(
        'columns' => array(),
        'headerPosition' => 'top',
        'noItemsMessage' => 'There are no items to display',
        'tableOptions' => array('cellpadding' => 0, 'cellspacing' => 0, 'border' => 0),
        'trOptions' => array(),
        'paginate' => array(
            'options' => array(),
            'prev' => array(
                'title' => '« Previous ',
                'options' => array(),
                'disabledTitle' => null,
                'disabledOptions' => array('class' => 'disabled')
            ),
            'numbers' => array(
                'options' => array(
                    'before' => ' &nbsp; ',
                    'after' => ' &nbsp; ',
                    'modulus' => 10,
                    'separator' => ' &nbsp; ',
                    'tag' => 'span',
                    'first' => 'first',
                    'last' => 'last',
                    'ellipsis' => '...'
                )
            ),
            'next' => array(
                'title' => ' Next »',
                'options' => array(),
                'disabledTitle' => null,
                'disabledOptions' => array('class' => 'disabled')
            ),
            'position' => 'bottom',
            'trOptions' => array('class' => 'paginator'),
            'tdOptions' => array('align' => 'center')
        )
    );

/**
 * Column default options.
 * `value` (string): Cell's content. You can use special tags, see TableHelper::_renderCell().
 * `thOptions` (array): <th> tag attributes for header cells (between <thead></thead>).
 * `tdOptions` (array): <td> tag attributes for body cells (for each row of content between <tbody></tbody>).
 * `sort` (mixed): Set to a string indicating the column name (`Model.column`). Set to (boolean) FALSE for do not sort this column.
 *
 * @var array
 */
    private $__columnDefaults = array(
        'value' => '',
        'thOptions' => array('align' => 'left'),
        'tdOptions' => array('align' => 'left'),
        'sort' => false
    );

/**
 * Holds the number of columns of the table being rendered.
 *
 * @var integer
 */
    private $__colsCount = 0;

/**
 * Renders out HTML table.
 *
 * @param array $data Data to fill table rows
 * @param array $options Table options.
 * @return string HTML table element
 * @see TableHelper::$__defaults
 */
    public function create($data, $options) {
        $this->__defaults['paginate']['prev']['title'] = __t('« Previous ');
        $this->__defaults['paginate']['next']['title'] = __t(' Next »');

        if (isset($options['paginate']) && $options['paginate'] === true) {
            unset($options['paginate']);
        } else {
            $this->__defaults['paginate'] = !isset($options['paginate']) ? false : $this->__defaults['paginate'];
        }

        $options = Set::merge($this->__defaults, $options);
        $this->__colsCount = count($options['columns']);
        $out = sprintf('<table%s>', $this->Html->_parseAttributes($options['tableOptions'])) . "\n";

        if (count($data) > 0) {

            $print_header_top = ($options['headerPosition'] !== false && in_array($options['headerPosition'], array('top', 'top&bottom')));
            $print_paginator_top = ($options['paginate'] !== false && in_array($options['paginate']['position'], array('top', 'top&bottom')));

            if ($print_header_top ||  $print_paginator_top) {
                $out .= "\t<thead>\n";
                $out .= $print_header_top ? $this->_renderHeader($options) : '';
                $out .= $print_paginator_top ? $this->_renderPaginator($options) : '';
                $out .= "\n\t</thead>\n";
            }

            $out .= "\t<tbody>\n";
            $count = 1;

            foreach ($data as $i => $r_data) {
                $td = '';

                foreach ($options['columns'] as $name => $c_data) {
                    $c_data = array_merge($this->__columnDefaults, $c_data);

                    $td .= "\n\t";
                    $td .= $this->Html->useTag('tablecell',
                            $this->Html->_parseAttributes($c_data['tdOptions']),
                            $this->_renderCell($c_data['value'], $data[$i])
                    );
                    $td .= "\t";
                }

                $tr_options = array(
                    'class' => ($count%2 ? 'even' : 'odd')
                );

                if (!empty($options['trOptions']) && is_array($options['trOptions'])) {
                    foreach ($options['trOptions'] as $key => $val) {
                        $val = $this->_renderCell($val, $data[$i]);

                        if ($key == 'class') {
                            $tr_options['class'] = $tr_options['class'] . " {$val}";
                        } else {
                            $tr_options[$key] = $val;
                        }
                    }
                }

                $out .= $this->Html->useTag('tablerow', $this->Html->_parseAttributes($tr_options), $td);
                $count++;
            }

            $out .= "\t</tbody>\n";
            $print_header_bottom = ($options['headerPosition'] !== false && in_array($options['headerPosition'], array('bottom', 'top&bottom')));
            $print_paginator_bottom = ($options['paginate'] != false && in_array($options['paginate']['position'], array('bottom', 'top&bottom')));

            if ($print_header_bottom || $print_paginator_bottom) {
                $out .= "\t<tfoot>\n";
                $out .= $print_header_bottom ? $this->_renderHeader($options) : '';
                $out .= $print_paginator_bottom ? $this->_renderPaginator($options) : '';
                $out .= "\n\t</tfoot>\n";
            }
        } else {
            $td   = $this->Html->useTag('tablecell', $this->Html->_parseAttributes(array('colspan' => $this->__colsCount)), __t($options['noItemsMessage']));
            $out .= $this->Html->useTag('tablerow', $this->Html->_parseAttributes(array('class' => 'even')), $td);
        }

        $out .= "</table>\n";

        return $out;
    }

/**
 * Render the given cell.
 * Looks for special tags to be replaced, valid tags are:
 *  - URL. e.g.: {url}/my/url.html{url}
 *  - Array path. e.g.: {Node.slug}
 *  - Image. e.g.: {img class='width' border=0}/url/to/image.jpg{/img}
 *  - Link. e.g.: {link class='css-class' title='Link title'}Link label|/link/url.html{/link}
 *  - Translation __t(). e.g.: {t}Translate this{/t}
 *  - Translation __d(). e.g.: {d|System}System module will translate this{/d}
 *  - PHP code. e.g.: {php} return 'Testing'; {/php}
 *
 * @param string $value Cell content
 * @param array $row_data Array of data of the row that cell belongs to
 * @return string HTML table cell content
 */
    protected function _renderCell($value, $row_data) {
        // look for urls. e.g.: {url}/my/url.html{url}
        preg_match_all('/\{url\}(.+)\{\/url\}/iUs', $value, $url);
        if (isset($url[1]) && !empty($url[1])) {
            foreach ($url[0] as $i => $m) {
                $value = str_replace($m, $this->Html->url(trim($url[1][$i]), true), $value);
            }
        }

        // look for array paths. e.g.: {Node.slug}
        preg_match_all('/\{([\{\}0-9a-zA-Z_\.]+)\}/iUs', $value, $path);
        if (isset($path[1]) && !empty($path[1])) {
            $exclude = array('{d}', '{/d}', '{t}', '{/t}', '{php}', '{/php}', '{img}', '{/img}', '{link}', '{/link}');

            foreach ($path[0] as $i => $m) {
                if (in_array($m, $exclude)) {
                    continue;
                }

                $value = str_replace($m, Set::extract(trim($path[1][$i]), $row_data), $value);
            }
        }

        // look for images. {img class='width' border=0}/url/to/image.jpg{/img}
        preg_match_all('/\{img(.*?)\}(.+)\{\/img\}/i', $value, $img);
        if (isset($img[1]) && !empty($img[1])) {
            foreach ($img[0] as $i => $m) {
                $opts = isset($img[1][$i]) ? $this->__parseAtts(trim($img[1][$i])) : array();
                $opts = empty($opts) ? array(): $opts;
                $value = str_replace($m, $this->Html->image($img[2][$i], $opts), $value);
            }
        }

        // look for links. e.g..: {link class='css-class' title='Link title'}Link label|/link/url.html{/link}
        preg_match_all('/\{link(.*?)\}(.*)\|(.*)\{\/link\}/i', $value, $link);
        if (isset($link[1]) && !empty($link[1])) {
            foreach ($link[0] as $i => $m) {
                $opts = isset($link[1][$i]) ? $this->__parseAtts(trim($link[1][$i])) : array();
                $opts = empty($opts) ? array(): $opts;

                if (isset($opts['escape'])) {
                    $opts['escape'] = $opts['escape'] == "true";
                }

                $value = str_replace($m, $this->Html->link(trim($link[2][$i]), $link[3][$i], $opts), $value);
            }
        }

        // look for __t(). e.g.: {t}Translate this{/t}
        preg_match_all('/\{t\}(.+)\{\/t\}/i', $value, $t);
        if (isset($t[1]) && !empty($t[1])) {
            foreach ($t[0] as $i => $m) {
                $value = str_replace($m, __t($t[1][$i]), $value);
            }
        }

        // look for __d(). e.g.: {d|System}System module will translate this{/d}
        preg_match_all('/\{d\|(.+)\}(.+)\{\/d\}/i', $value, $d);
        if (isset($d[1]) && !empty($d[1])) {
            foreach ($d[0] as $i => $m) {
                $value = str_replace($m, __d($d[1][$i], $d[2][$i]), $value);
            }
        }

        // look for php code. e.g.: {php} return 'Testing'; {/php}
        preg_match_all('/\{php\}(.+)\{\/php\}/iUs', $value, $php);
        if (isset($php[1]) && !empty($php[1])) {
            foreach ($php[0] as $i => $m) {
                $value = str_replace($m, $this->__php_eval("<?php {$php[1][$i]}", $row_data), $value);
            }
        }

        return $value;
    }

/**
 * Parse tag attributes.
 *
 * @param string $text Tag string to parse
 * @return array Array of attributes
 */
    private function __parseAtts($text) {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);

        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) and strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                }
            }
        } else {
            $atts = ltrim($text);
        }

        return $atts;
    }

/**
 * Evaluate a string of PHP code.
 *
 * This is a wrapper around PHP's eval(). It uses output buffering to capture both
 * returned and printed text. Unlike eval(), we require code to be surrounded by
 * <?php ?> tags; in other words, we evaluate the code as if it were a stand-alone
 * PHP file.
 *
 * Using this wrapper also ensures that the PHP code which is evaluated can not
 * overwrite any variables in the calling code, unlike a regular eval() call.
 *
 * @param string $code The code to evaluate.
 * @return
 *   A string containing the printed output of the code, followed by the returned
 *   output of the code.
 *
 */
    private function __php_eval($code, $row_data = array()) {
        ob_start();
        print eval('?>' . $code);

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }

/**
 * Renders table's header.
 *
 * @return string HTML
 */
    protected function _renderHeader($options, $footer = false) {
        $th = $out ='';

        if ($footer && $options['paginate'] !== false && in_array($options['paginate']['position'], array('top', 'top&bottom'))) {
            @$out .= $this->_renderPaginator($options);
        }

        foreach ($options['columns'] as $name => $data) {
            $data = array_merge($this->__columnDefaults, $data);

            if ($options['paginate'] !== false && is_string($data['sort'])) {
                @$name = $this->Paginator->sort($data['sort'], $name);
            }

            $th .= "\t\t". $this->Html->useTag('tableheader', $this->Html->_parseAttributes($data['thOptions']), $name) . "\n";
        }

        $out .= $this->Html->useTag('tablerow', null, $th);

        return $out;
    }

/**
 * Renders table's pagination-row.
 *
 * @return string HTML
 */
    protected function _renderPaginator($array) {
        $out = $paginator = '';
        $array = $array['paginate'];
        $paginator .= $this->Paginator->options($array['options']);
        $paginator .= $this->Paginator->prev($array['prev']['title'], $array['prev']['options'], $array['prev']['disabledTitle'], $array['prev']['disabledOptions']);
        $paginator .= $this->Paginator->numbers($array['numbers']['options']);
        $paginator .= $this->Paginator->next($array['next']['title'], $array['next']['options'], $array['next']['disabledTitle'], $array['next']['disabledOptions']);
        $td = $this->Html->useTag('tablecell', $this->Html->_parseAttributes(array_merge(array('colspan' => $this->__colsCount), $array['tdOptions'])), $paginator);
        $out .= $this->Html->useTag('tablerow', $this->Html->_parseAttributes($array['trOptions']), $td);

        return $out;
    }
}