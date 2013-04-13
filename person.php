<?php
/**
 * Person class, holding configuration for a person
 *
 * @copyright copyright info
 * @license   license link
 * @version   version info
 */
class Person
{
    /**
     * storage for the dynamic data fields
     *
     * @var array
     */
    private $fields;

    /**
     * name of the idcard template to use
     *
     * @var string
     */
    private $template;

    /**
     * x offset of embedded picture
     *
     * @var int
     */
    private $x;

    /**
     * y offset of embedded picture
     *
     * @var int
     */
    private $y;

    /**
     * value for red for background filling
     *
     * @var int
     */
    private $bg_red;

    /**
     * value for green for background filling
     *
     * @var int
     */
    private $bg_green;

    /**
     * value for blue for background filling
     *
     * @var int
     */
    private $bg_blue;

    /**
     * data fields with extra info about the card
     *
     * @var array
     */
    private $data_fields;

    /**
     * filename of the photo for the persons id card
     *
     * @var string
     */
    private $filename;

    /**
     * public constructor
     *
     * @param string $filename    Filename to output image to
     * @param array  $data_fields Fields with extra data (like text for the card)
     * @param array  $defaults    Default values for various properties
     *
     * @access public
     * @return void
     */
    public function __construct($filename, array $data_fields, array $defaults)
    {
        $this->x        = $defaults['x'];
        $this->y        = $defaults['y'];
        $this->template = $defaults['template'];
        $this->filename = $filename;
        $this->bg_r     = "";
        $this->bg_g     = "";
        $this->bg_b     = "";

        $this->data_fields = $data_fields;

        foreach ($data_fields as $field) {
            $this->fields[$field] = '';
        }
    }

    /**
     * Sets the config for the person
     *
     * @param array $config Config with options for the person
     *
     * @access public
     * @return $this
     */
    public function setConfig($config)
    {
        $this->filename = $config['filename'];
        $this->x        = intval($config['x']);
        $this->y        = intval($config['y']);
        $this->template = $config['template'];
        $this->bg_r     = !isset($config['bg_r']) || !strlen($config['bg_r']) ? '' : intval($config['bg_r']);
        $this->bg_g     = !isset($config['bg_g']) || !strlen($config['bg_g']) ? '' : intval($config['bg_g']);
        $this->bg_b     = !isset($config['bg_b']) || !strlen($config['bg_b']) ? '' : intval($config['bg_b']);

        foreach ($this->data_fields as $field) {
            if (!empty($config[$field])) {
                $this->fields[$field] = $config[$field];
            }
        }

        return $this;
    }

    /**
     * returns the configuration for the person
     *
     * @param type $variable Description
     *
     * @access public
     * @return array
     */
    public function getConfig()
    {
        $config = array(
            'filename' => $this->filename,
            'bg_r'     => $this->bg_r,
            'bg_g'     => $this->bg_g,
            'bg_b'     => $this->bg_b,
            'x'        => $this->x,
            'y'        => $this->y,
            'template' => $this->template,
        );

        foreach ($this->data_fields as $field) {
            $config[$field] = !empty($this->fields[$field]) ? $this->fields[$field] : '';
        }

        return $config;
    }

    /**
     * updates the config for the person instance
     *
     * @param type array $post_data Description
     *
     * @access public
     * @return $this
     */
    public function updateConfig(array $post_data)
    {
        $this->x = intval($post_data['x']);
        $this->y = intval($post_data['y']);

        $this->bg_r = $post_data['bg_r'];
        $this->bg_g = $post_data['bg_g'];
        $this->bg_b = $post_data['bg_b'];

        if (!empty($post_data['template'])) {
            $this->template = $post_data['template'];
        }

        foreach ($this->data_fields as $field) {
            if (!empty($post_data[$field])) {
                $this->fields[$field] = $post_data[$field];
            }
        }

        return $this;
    }

    /**
     * returns the template used for the persons id card
     *
     * @access public
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * returns the x coordinate of the persons embedded picture
     *
     * @access public
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * returns the y coordinate of the persons embedded picture
     *
     * @access public
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * returns the value for a dynamic field
     *
     * @param string $field Field to return value for
     *
     * @access public
     * @return string
     */
    public function getField($field)
    {
        return !empty($this->fields[$field]) ? $this->fields[$field] : '';
    }

    /**
     * returns the value for a background override color
     *
     * @param string $color Color to get value for
     *
     * @access public
     * @return int|string
     */
    public function getBgColor($color)
    {
        switch ($color) {
        case 'red':
            return $this->bg_red;

        case 'green':
            return $this->bg_green;

        case 'blue':
            return $this->bg_blue;
        }

        return '';
    }

    /**
     * returns the filename of the person
     *
     * @access public
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * tries to convert the filename to a normal name
     *
     * @access public
     * @return string
     */
    public function turnFilenameToName()
    {
        $parts = explode('-', $this->getFilename());
        return ucwords(trim($parts[0]));
    }

    /**
     * tries to convert the filename to a function
     * i.e. checks for a second part in the filename,
     * after a hyphen but before the postfix
     *
     * @access public
     * @return string
     */
    public function turnFilenameToFunction()
    {
        $parts = explode('-', $this->getFilename());
        if (isset($parts[1])) {
            return ucwords(trim(preg_replace('/\.(jpg|jpeg|png)$/i', '', $parts[1])));
        }

        return '';
    }
}
