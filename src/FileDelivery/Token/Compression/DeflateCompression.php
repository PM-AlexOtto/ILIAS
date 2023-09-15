<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\FileDelivery\Token\Compression;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DeflateCompression implements Compression
{
    private const LEVEL = 9;

    /**
     * @throws CompressionFailure
     */
    public function compress(string $data): string
    {
        $compressed = \gzdeflate($data, self::LEVEL);
        if ($compressed === false) {
            throw new CompressionFailure();
        }
        return $compressed;
    }

    /**
     * @throws CompressionFailure
     */
    public function decompress(string $data): string
    {
        $decompressed = \gzinflate($data);
        if ($decompressed === false) {
            throw new CompressionFailure();
        }

        return $decompressed;
    }
}
