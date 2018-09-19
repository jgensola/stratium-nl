<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
namespace Dompdf\FrameReflower;

use Dompdf\FontMetrics;
use Dompdf\Frame;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\TableCell as TableCellFrameDecorator;
use Dompdf\FrameDecorator\Text as TextFrameDecorator;
use Dompdf\Exception;
use Dompdf\Css\Style;

/**
 * Reflows block frames
 *
 * @package dompdf
 */
class Block extends AbstractFrameReflower
{
    // Minimum line width to justify, as fraction of available width
    const MIN_JUSTIFY_WIDTH = 0.80;

    /**
     * @var BlockFrameDecorator
     */
    protected $_frame;

    function __construct(BlockFrameDecorator $frame)
    {
        parent::__construct($frame);
    }

    /**
     *  Calculate the ideal used value for the width property as per:
     *  http://www.w3.org/TR/CSS21/visudet.html#Computing_widths_and_margins
     *
     * @param float $width
     *
     * @return array
     */
    protected function _calculate_width($width)
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $w = $frame->get_containing_block("w");

        if ($style->position === "fixed") {
            $w = $frame->get_parent()->get_containing_block("w");
        }

        $rm = $style->length_in_pt($style->margin_right, $w);
        $lm = $style->length_in_pt($style->margin_left, $w);

        $left = $style->length_in_pt($style->left, $w);
        $right = $style->length_in_pt($style->right, $w);

        // Handle 'auto' values
        $dims = array($style->border_left_width,
            $style->border_right_width,
            $style->padding_left,
            $style->padding_right,
            $width !== "auto" ? $width : 0,
            $rm !== "auto" ? $rm : 0,
            $lm !== "auto" ? $lm : 0);

        // absolutely positioned boxes take the 'left' and 'right' properties into account
        if ($frame->is_absolute()) {
            $absolute = true;
            $dims[] = $left !== "auto" ? $left : 0;
            $dims[] = $right !== "auto" ? $right : 0;
        } else {
            $absolute = false;
        }

        $sum = (float)$style->length_in_pt($dims, $w);

        // Compare to the containing block
        $diff = $w - $sum;

        if ($diff > 0) {
            if ($absolute) {
                // resolve auto properties: see
                // http://www.w3.org/TR/CSS21/visudet.html#abs-non-replaced-width

                if ($width === "auto" && $left === "auto" && $right === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }

                    // Technically, the width should be "shrink-to-fit" i.e. based on the
                    // preferred width of the content...  a little too costly here as a
                    // special case.  Just get the width to take up the slack:
                    $left = 0;
                    $right = 0;
                    $width = $diff;
                } else if ($width === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }
                    if ($left === "auto") {
                        $left = 0;
                    }
                    if ($right === "auto") {
                        $right = 0;
                    }

                    $width = $diff;
                } else if ($left === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }
                    if ($right === "auto") {
                        $right = 0;
                    }

                    $left = $diff;
                } else if ($right === "auto") {
                    if ($lm === "auto") {
                        $lm = 0;
                    }
                    if ($rm === "auto") {
                        $rm = 0;
                    }

                    $right = $diff;
                }

            } else {
                // Find auto properties and get them to take up the slack
                if ($width === "auto") {
                    $width = $diff;
                } else if ($lm === "auto" && $rm === "auto") {
                    $lm = $rm = round($diff / 2);
                } else if ($lm === "auto") {
                    $lm = $diff;
                } else if ($rm === "auto") {
                    $rm = $diff;
                }
            }
        } else if ($diff < 0) {
            // We are over constrained--set margin-right to the difference
            $rm = $diff;
        }

        return array(
            "width" => $width,
            "margin_left" => $lm,
            "margin_right" => $rm,
            "left" => $left,
            "right" => $right,
        );
    }

    /**
     * Call the above function, but resolve max/min widths
     *
     * @throws Exception
     * @return array
     */
    protected function _calculate_restricted_width()
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $cb = $frame->get_containing_block();

        if ($style->position === "fixed") {
            $cb = $frame->get_root()->get_containing_block();
        }

        //if ( $style->position === "absolute" )
        //  $cb = $frame->find_positionned_parent()->get_containing_block();

        if (!isset($cb["w"])) {
            throw new Exception("Box property calculation requires containing block width");
        }

        // Treat width 100% as auto
        if ($style->width === "100%") {
            $width = "auto";
        } else {
            $width = $style->length_in_pt($style->width, $cb["w"]);
        }

        $calculate_width = $this->_calculate_width($width);
        $margin_left = $calculate_width['margin_left'];
        $margin_right = $calculate_width['margin_right'];
        $width =  $calculate_width['width'];
        $left =  $calculate_width['left'];
        $right =  $calculate_width['right'];

        // Handle min/max width
        $min_width = $style->length_in_pt($style->min_width, $cb["w"]);
        $max_width = $style->length_in_pt($style->max_width, $cb["w"]);

        if ($max_width !== "none" && $min_width > $max_width) {
            list($max_width, $min_width) = array($min_width, $max_width);
        }

        if ($max_width !== "none" && $width > $max_width) {
            extract($this->_calculate_width($max_width));
        }

        if ($width < $min_width) {
            $calculate_width = $this->_calculate_width($min_width);
            $margin_left = $calculate_width['margin_left'];
            $margin_right = $calculate_width['margin_right'];
            $width =  $calculate_width['width'];
            $left =  $calculate_width['left'];
            $right =  $calculate_width['right'];
        }

        return array($width, $margin_left, $margin_right, $left, $right);
    }

    /**
     * Determine the unrestricted height of content within the block
     * not by adding each line's height, but by getting the last line's position.
     * This because lines could have been pushed lower by a clearing element.
     *
     * @return float
     */
    protected function _calculate_content_height()
    {
        $height = 0;
        $lines = $this->_frame->get_line_boxes();
        if (count($lines) > 0) {
            $last_line = end($lines);
            $content_box = $this->_frame->get_content_box();
            $height = $last_line->y + $last_line->h - $content_box["y"];
        }
        return $height;
    }

    /**
     * Determine the frame's restricted height
     *
     * @return array
     */
    protected function _calculate_restricted_height()
    {
        $frame = $this->_frame;
        $style = $frame->get_style();
        $content_height = $this->_calculate_content_height();
        $cb = $frame->get_containing_block();

        $height = $style->length_in_pt($style->height, $cb["h"]);

        $top = $style->length_in_pt($style->top, $cb["h"]);
        $bottom = $style->length_in_pt($style->bottom, $cb["h"]);

        $margin_top = $style->length_in_pt($style->margin_top, $cb["h"]);
        $margin_bottom = $style->length_in_pt($style->margin_bottom, $cb["h"]);

        if ($frame->is_absolute()) {

            // see http://www.w3.org/TR/CSS21/visudet.html#abs-non-replaced-height

            $dims = array($top !== "auto" ? $top : 0,
                $style->margin_top !== "auto" ? $style->margin_top : 0,
                $style->padding_top,
                $style->border_top_width,
                $height !== "auto" ? $height : 0,
                $style->border_bottom_width,
                $style->padding_bottom,
                $style->margin_bottom !== "auto" ? $style->margin_bottom : 0,
                $bottom !== "auto" ? $bottom : 0);

            $sum = (float)$style->length_in_pt($dims, $cb["h"]);

            $diff = $cb["h"] - $sum;

            if ($diff > 0) {
                if ($height === "auto" && $top === "auto" && $bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $diff;
                } else if ($height === "auto" && $top === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $content_height;
                    $top = $diff - $content_height;
                } else if ($height === "auto" && $bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $content_height;
                    $bottom = $diff - $content_height;
                } else if ($top === "auto" && $bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $bottom = $diff;
                } else if ($top === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $top = $diff;
                } else if ($height === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $height = $diff;
                } else if ($bottom === "auto") {
                    if ($margin_top === "auto") {
                        $margin_top = 0;
                    }
                    if ($margin_bottom === "auto") {
                        $margin_bottom = 0;
                    }

                    $bottom = $diff;
                } else {
                    if ($style->overflow === "visible") {
                        // set all autos to zero
                        if ($margin_top === "auto") {
                            $margin_top = 0;
                        }
                        if ($margin_bottom === "auto") {
                            $margin_bottom = 0;
                        }
                        if ($top === "auto") {
                            $top = 0;
                        }
                        if ($bottom === "auto") {
                            $bottom = 0;
                        }
                        if ($height === "auto") {
                            $height = $content_height;
                        }
                    }

                    // FIXME: overflow hidden
                }
            }

        } else {
            // Expand the height if overflow is visible
            if ($height === "auto" && $content_height > $height /* && $style->overflow === "visible" */) {
                $height = $content_height;
            }

            // FIXME: this should probably be moved to a seperate function as per
            // _calculate_restricted_width

            // Only handle min/max height if the height is independent of the frame's content
            if (!($style->overflow === "visible" ||
                ($style->overflow === "hidden" && $height === "auto"))
            ) {

                $min_height = $style->min_height;
                $max_height = $style->max_height;

                if (isset($cb["h"])) {
                    $min_height = $style->length_in_pt($min_height, $cb["h"]);
                    $max_height = $style->length_in_pt($max_height, $cb["h"]);

                } else if (isset($cb["w"])) {
                    if (mb_strpos($min_height, "%") !== false) {
                        $min_height = 0;
                    } else {
                        $min_height = $style->length_in_pt($min_height, $cb["w"]);
                    }

                    if (mb_strpos($max_height, "%") !== false) {
                        $max_height = "none";
                    } else {
                        $max_height = $style->length_in_pt($max_height, $cb["w"]);
                    }
                }

                if ($max_height !== "none" && $min_height > $max_height) {
                    // Swap 'em
                    list($max_height, $min_height) = array($min_height, $max_height);
                }

                if ($max_height !== "none" && $height > $max_height) {
                    $height = $max_height;
                }

                if ($height < $min_height) {
                    $height = $min_height;
                }
            }
        }

        return array($height, $margin_top, $margin_bottom, $top, $bottom);
    }

    /**
     * Adjust the justification of each of our lines.
     * http://www.w3.org/TR/CSS21/text.html#propdef-text-align
     */
    protected function _text_align()
    {
        $style = $this->_frame->get_style();
        $w = $this->_frame->get_containing_block("w");
        $width = (float)$style->length_in_pt($style->width, $w);

        switch ($style->text_align) {
            default:
            case "left":
                foreach ($this->_frame->get_line_boxes() as $line) {
                    if (!$line->left) {
                        continue;
                    }

                    foreach ($line->get_frames() as $frame) {
                        if ($frame instanceof BlockFrameDecorator) {
                            continue;
                        }
                        $frame->set_position($frame->get_position("x") + $line->left);
                    }
                }
                return;

            case "right":
                foreach ($this->_frame->get_line_boxes() as $line) {
                    // Move each child over by $dx
                    $dx = $width - $line->w - $line->right;

                    foreach ($line->get_frames() as $frame