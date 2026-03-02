<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\YouTube;

class LiveChatGiftDetails extends \Google\Model
{
  /**
   * The alternative text to be used for accessibility.
   *
   * @var string
   */
  public $altText;
  /**
   * The number of times the gift has been sent in a row.
   *
   * @var int
   */
  public $comboCount;
  /**
   * The duration of the gift.
   *
   * @var string
   */
  public $giftDuration;
  /**
   * The name of the gift.
   *
   * @var string
   */
  public $giftName;
  /**
   * The URL of the gift image.
   *
   * @var string
   */
  public $giftUrl;
  /**
   * Whether the gift involves a visual effect.
   *
   * @var bool
   */
  public $hasVisualEffect;
  /**
   * The cost of the gift in jewels.
   *
   * @var int
   */
  public $jewelsCount;
  /**
   * The BCP-47 language code of the gift.
   *
   * @var string
   */
  public $language;

  /**
   * The alternative text to be used for accessibility.
   *
   * @param string $altText
   */
  public function setAltText($altText)
  {
    $this->altText = $altText;
  }
  /**
   * @return string
   */
  public function getAltText()
  {
    return $this->altText;
  }
  /**
   * The number of times the gift has been sent in a row.
   *
   * @param int $comboCount
   */
  public function setComboCount($comboCount)
  {
    $this->comboCount = $comboCount;
  }
  /**
   * @return int
   */
  public function getComboCount()
  {
    return $this->comboCount;
  }
  /**
   * The duration of the gift.
   *
   * @param string $giftDuration
   */
  public function setGiftDuration($giftDuration)
  {
    $this->giftDuration = $giftDuration;
  }
  /**
   * @return string
   */
  public function getGiftDuration()
  {
    return $this->giftDuration;
  }
  /**
   * The name of the gift.
   *
   * @param string $giftName
   */
  public function setGiftName($giftName)
  {
    $this->giftName = $giftName;
  }
  /**
   * @return string
   */
  public function getGiftName()
  {
    return $this->giftName;
  }
  /**
   * The URL of the gift image.
   *
   * @param string $giftUrl
   */
  public function setGiftUrl($giftUrl)
  {
    $this->giftUrl = $giftUrl;
  }
  /**
   * @return string
   */
  public function getGiftUrl()
  {
    return $this->giftUrl;
  }
  /**
   * Whether the gift involves a visual effect.
   *
   * @param bool $hasVisualEffect
   */
  public function setHasVisualEffect($hasVisualEffect)
  {
    $this->hasVisualEffect = $hasVisualEffect;
  }
  /**
   * @return bool
   */
  public function getHasVisualEffect()
  {
    return $this->hasVisualEffect;
  }
  /**
   * The cost of the gift in jewels.
   *
   * @param int $jewelsCount
   */
  public function setJewelsCount($jewelsCount)
  {
    $this->jewelsCount = $jewelsCount;
  }
  /**
   * @return int
   */
  public function getJewelsCount()
  {
    return $this->jewelsCount;
  }
  /**
   * The BCP-47 language code of the gift.
   *
   * @param string $language
   */
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  /**
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LiveChatGiftDetails::class, 'Google_Service_YouTube_LiveChatGiftDetails');
